<?php

namespace Tests\Feature;

use App\Models\Fault;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FullSystemFlowTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Pass12@word';

    public function test_core_admin_manager_customer_technician_flow(): void
    {
        $this->post('/register/admin', [
            'name' => 'System Admin',
            'email' => 'system.admin@ttcl.co.tz',
            'phone' => '0730000001',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertRedirect('/login');

        $this->assertDatabaseHas('users', [
            'email' => 'system.admin@ttcl.co.tz',
            'role' => 'admin',
            'is_approved' => true,
        ]);

        $this->get('/register/admin')->assertRedirect('/login');

        $this->post('/register/manager', [
            'name' => 'Dar Manager',
            'email' => 'dar.manager@ttcl.co.tz',
            'phone' => '0730000002',
            'region' => 'Dar es Salaam',
            'district' => 'Ilala',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertRedirect('/login');

        $manager = User::where('email', 'dar.manager@ttcl.co.tz')->firstOrFail();
        $this->assertSame('manager', $manager->role);
        $this->assertFalse($manager->is_approved);

        $this->post('/login', [
            'email' => 'dar.manager@ttcl.co.tz',
            'password' => self::PASSWORD,
        ])->assertRedirect('/login')
            ->assertSessionHas('success', 'Wait for admin approval before login. It will take not more than 24 hours.');
        $this->assertGuest();

        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)
            ->post('/admin/manager/approve/' . $manager->id)
            ->assertRedirect();

        $this->assertTrue($manager->fresh()->is_approved);

        $this->post('/login', [
            'email' => 'dar.manager@ttcl.co.tz',
            'password' => self::PASSWORD,
        ])->assertRedirect('/manager/dashboard');

        $this->actingAs($manager->fresh())
            ->post('/manager/users/store', [
                'role' => 'technician',
                'name' => 'Dar Technician',
                'email' => 'dar.technician@ttcl.co.tz',
                'phone' => '0730000003',
                'region' => 'Dar es Salaam',
                'district' => 'Ilala',
                'tech_base' => 'Exchange A',
                'password' => self::PASSWORD,
            ])->assertRedirect();

        $technician = User::where('email', 'dar.technician@ttcl.co.tz')->firstOrFail();
        $this->assertSame('Dar es Salaam', $technician->region);
        $this->assertTrue($technician->is_approved);

        $this->post('/register/customer', [
            'name' => 'Dar Customer',
            'email' => 'dar.customer@gmail.com',
            'phone' => '0710000001',
            'region' => 'Dar es Salaam',
            'district' => 'Ilala',
            'ward' => 'Kariakoo',
            'street' => 'Market',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertRedirect('/login');

        $customer = User::where('email', 'dar.customer@gmail.com')->firstOrFail();
        $this->assertFalse($customer->is_approved);

        $this->actingAs($manager->fresh())
            ->post('/manager/user/approve/' . $customer->id)
            ->assertRedirect();

        $this->assertTrue($customer->fresh()->is_approved);

        $this->actingAs($customer->fresh())
            ->post('/customer/fault/store', [
                'type' => 'No internet',
                'description' => 'Internet is down at home',
                'contact_phone' => '0710000001',
            ])->assertRedirect('/customer/dashboard');

        $fault = Fault::where('user_id', $customer->id)->firstOrFail();
        $this->assertSame('Pending', $fault->status);

        $this->actingAs($manager->fresh())
            ->post('/manager/assign/' . $fault->id, [
                'technician_id' => $technician->id,
            ])->assertRedirect();

        $this->assertSame('In Progress', $fault->fresh()->status);
        $this->assertSame($technician->id, $fault->fresh()->technician_id);

        $this->actingAs($technician)
            ->post('/technician/update/' . $fault->id, [
                'status' => 'Resolved',
            ])->assertRedirect();

        $this->assertSame('Resolved', $fault->fresh()->status);
    }

    public function test_role_middleware_blocks_wrong_dashboard_access(): void
    {
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'phone' => '0710000002',
            'password' => Hash::make(self::PASSWORD),
            'role' => 'customer',
            'is_approved' => true,
        ]);

        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/manager/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/technician/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/customer/dashboard')->assertOk();
    }
}
