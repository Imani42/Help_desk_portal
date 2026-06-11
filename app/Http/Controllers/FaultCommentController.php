<?php

namespace App\Http\Controllers;

use App\Models\Fault;
use App\Models\FaultComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaultCommentController extends Controller
{
    public function store(Request $request, Fault $fault)
    {
        $user = Auth::user();

        abort_unless(
            ($user->role === 'customer' && $fault->user_id === $user->id) ||
            ($user->role === 'technician' && $fault->technician_id === $user->id),
            403
        );

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

        abort_unless($user->role === 'manager' && $comment->parent_id === null, 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        FaultComment::create([
            'fault_id' => $comment->fault_id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
            'role' => 'manager',
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Reply sent');
    }

    public function destroy(FaultComment $comment)
    {
        $user = Auth::user();

        abort_unless(
            $user->role === 'manager' || $user->id === $comment->user_id,
            403
        );

        $comment->delete();

        return back()->with('success', 'Comment deleted');
    }
}
