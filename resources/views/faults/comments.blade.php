@php
    $commentContext = $context ?? auth()->user()->role;
    $messages = collect();

    foreach ($fault->comments as $comment) {
        $messages->push((object)[
            'id' => $comment->id,
            'role' => $comment->role,
            'author' => $comment->author,
            'body' => $comment->body,
            'created_at' => $comment->created_at,
            'is_reply' => false,
            'parent_id' => null,
            'user_id' => $comment->user_id,
        ]);

        foreach ($comment->replies as $reply) {
            $messages->push((object)[
                'id' => $reply->id,
                'role' => $reply->role,
                'author' => $reply->author,
                'body' => $reply->body,
                'created_at' => $reply->created_at,
                'is_reply' => true,
                'parent_id' => $comment->id,
                'user_id' => $reply->user_id,
            ]);
        }
    }

    $messages = $messages->sortBy('created_at');
@endphp

<div class="comments-box">
    <div class="comments-header">
        <div>
            <strong>Comments ({{ $messages->count() }})</strong>
            <p class="comments-subtitle">Conversation stream for this fault</p>
        </div>
    </div>

    <div class="comments-scroll">
        @php $currentUser = auth()->user(); @endphp
        @forelse($messages as $message)
            <div class="message-item {{ $message->role }} {{ $message->is_reply ? 'reply-message' : '' }}">
                <div class="message-bubble">
                    <div class="message-meta">
                        <div class="message-author">
                            <span class="role-badge">{{ $message->role === 'manager' ? 'Manager' : ucfirst($message->role) }}</span>
                            @if($message->author)
                                <span>{{ $message->author->name }}</span>
                            @endif
                        </div>
                        <time>{{ $message->created_at->format('d M Y H:i') }}</time>
                    </div>
                    <p>{{ $message->body }}</p>
                </div>
                @if($currentUser && $currentUser->role === 'manager')
                    <form method="POST" action="/fault-comments/{{ $message->id }}" class="delete-comment-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-comment-button">Delete</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="comment-empty-card">
                <strong>No comments yet</strong>
                <p>There are no conversations on this fault yet. Start the dialogue with a clear comment below.</p>
            </div>
        @endforelse
    </div>

    @if(in_array($commentContext, ['customer', 'technician', 'manager']) && ! $fault->comments->count())
        <form method="POST"
              action="/faults/{{ $fault->id }}/comments"
              class="comment-form">
            @csrf
            <textarea
                name="body"
                placeholder="Write a comment about this fault..."
                required></textarea>
            <button type="submit">Send Comment</button>
        </form>
    @endif

    @if(in_array($commentContext, ['customer', 'technician', 'manager']) && $fault->comments->count())
        @php $conversation = $fault->comments->first(); @endphp
        <div class="reply-section">
            <p class="reply-target">
                Continue conversation
            </p>
            <form method="POST"
                  action="/fault-comments/{{ $conversation->id }}/reply"
                  class="comment-form">
                @csrf
                <textarea
                    name="body"
                    placeholder="Write a reply..."
                    required></textarea>
                <button type="submit">Send Reply</button>
            </form>
        </div>
    @endif
</div>
