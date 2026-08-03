<?php

namespace App\Http\Controllers;

use App\Models\Fault;
use App\Models\FaultComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaultCommentController extends Controller
{
    private function canAccessFault($user, Fault $fault): bool
    {
        if ($user->role === 'customer') {
            return $fault->user_id === $user->id;
        }

        if ($user->role === 'technician') {
            return $fault->technician_id === $user->id;
        }

        if ($user->role === 'manager') {
            $fault->loadMissing('reporter');

            return $fault->reporter
                && strcasecmp(trim((string) $fault->reporter->region), trim((string) $user->region)) === 0;
        }

        return false;
    }

    public function store(Request $request, Fault $fault)
    {
        $user = Auth::user();

        abort_unless($this->canAccessFault($user, $fault), 403);
        abort_if($fault->comments()->exists(), 409, 'This fault already has a conversation. Continue it with a reply.');

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        FaultComment::create([
            'fault_id' => $fault->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Comment sent');
    }

    public function reply(Request $request, FaultComment $comment)
    {
        $user = Auth::user();
        $comment->loadMissing('fault.reporter');

        abort_unless(
            $comment->parent_id === null && $this->canAccessFault($user, $comment->fault),
            403
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        FaultComment::create([
            'fault_id' => $comment->fault_id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
            'role' => $user->role,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Reply sent');
    }

    public function destroy(FaultComment $comment)
    {
        $user = Auth::user();
        $comment->loadMissing('fault.reporter');

        abort_unless(
            $this->canAccessFault($user, $comment->fault)
                && $user->role === 'manager',
            403
        );

        $comment->delete();

        return back()->with('success', 'Comment deleted');
    }
}
