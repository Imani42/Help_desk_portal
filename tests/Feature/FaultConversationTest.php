<?php

namespace Tests\Feature;

use App\Models\Fault;
use App\Models\FaultComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaultConversationTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $region = 'Arusha'): User
    {
        return User::factory()->create([
            'role' => $role,
            'region' => $region,
            'is_approved' => true,
        ]);
    }

    private function fault(User $customer, User $technician): Fault
    {
        return Fault::create([
            'user_id' => $customer->id,
            'technician_id' => $technician->id,
            'type' => 'Network outage',
            'description' => 'No service at the customer site.',
            'location' => 'Arusha',
            'status' => 'In Progress',
        ]);
    }

    public function test_technician_comment_is_visible_to_fault_customer_and_regional_manager(): void
    {
        $customer = $this->user('customer');
        $technician = $this->user('technician');
        $manager = $this->user('manager', 'arusha');
        $fault = $this->fault($customer, $technician);

        $this->actingAs($technician)
            ->post("/faults/{$fault->id}/comments", ['body' => 'The line has been tested.'])
            ->assertRedirect();

        $this->actingAs($customer)
            ->get('/customer/my-faults')
            ->assertOk()
            ->assertSee('The line has been tested.');

        $this->actingAs($manager)
            ->get('/manager/faults')
            ->assertOk()
            ->assertSee('The line has been tested.');
    }

    public function test_only_fault_participants_can_comment_or_reply(): void
    {
        $customer = $this->user('customer');
        $otherCustomer = $this->user('customer');
        $technician = $this->user('technician');
        $otherTechnician = $this->user('technician');
        $otherManager = $this->user('manager', 'Dodoma');
        $fault = $this->fault($customer, $technician);

        $comment = FaultComment::create([
            'fault_id' => $fault->id,
            'user_id' => $technician->id,
            'role' => 'technician',
            'body' => 'Work has started.',
        ]);

        foreach ([$otherCustomer, $otherTechnician, $otherManager] as $user) {
            $this->actingAs($user)
                ->post("/faults/{$fault->id}/comments", ['body' => 'Unauthorized'])
                ->assertForbidden();

            $this->actingAs($user)
                ->post("/fault-comments/{$comment->id}/reply", ['body' => 'Unauthorized'])
                ->assertForbidden();
        }
    }

    public function test_customer_and_technician_can_reply_within_their_fault_conversation(): void
    {
        $customer = $this->user('customer');
        $technician = $this->user('technician');
        $fault = $this->fault($customer, $technician);
        $comment = FaultComment::create([
            'fault_id' => $fault->id,
            'user_id' => $technician->id,
            'role' => 'technician',
            'body' => 'Please confirm whether service is back.',
        ]);

        $this->actingAs($customer)
            ->post("/fault-comments/{$comment->id}/reply", ['body' => 'Service is back now.'])
            ->assertRedirect();

        $this->assertDatabaseHas('fault_comments', [
            'fault_id' => $fault->id,
            'parent_id' => $comment->id,
            'user_id' => $customer->id,
            'role' => 'customer',
            'body' => 'Service is back now.',
        ]);
    }

    public function test_a_fault_uses_one_comment_then_continues_with_replies(): void
    {
        $customer = $this->user('customer');
        $technician = $this->user('technician');
        $fault = $this->fault($customer, $technician);

        $this->actingAs($technician)
            ->post("/faults/{$fault->id}/comments", ['body' => 'Initial update'])
            ->assertRedirect();

        $this->actingAs($customer)
            ->post("/faults/{$fault->id}/comments", ['body' => 'Second top-level comment'])
            ->assertStatus(409);

        $comment = FaultComment::where('fault_id', $fault->id)->whereNull('parent_id')->firstOrFail();

        $this->actingAs($customer)
            ->post("/fault-comments/{$comment->id}/reply", ['body' => 'Continued reply'])
            ->assertRedirect();

        $this->assertSame(1, FaultComment::where('fault_id', $fault->id)->whereNull('parent_id')->count());
        $this->assertSame(1, FaultComment::where('fault_id', $fault->id)->whereNotNull('parent_id')->count());
    }

    public function test_only_the_faults_regional_manager_can_delete_chat_messages(): void
    {
        $customer = $this->user('customer');
        $technician = $this->user('technician');
        $manager = $this->user('manager');
        $otherManager = $this->user('manager', 'Dodoma');
        $fault = $this->fault($customer, $technician);
        $comment = FaultComment::create([
            'fault_id' => $fault->id,
            'user_id' => $technician->id,
            'role' => 'technician',
            'body' => 'Technician update',
        ]);

        foreach ([$customer, $technician, $otherManager] as $user) {
            $this->actingAs($user)
                ->delete("/fault-comments/{$comment->id}")
                ->assertForbidden();
        }

        $this->actingAs($manager)
            ->delete("/fault-comments/{$comment->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('fault_comments', ['id' => $comment->id]);
    }
}
