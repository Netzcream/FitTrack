<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\MessagingService;
use App\Models\Tenant\Conversation;
use App\Enums\ParticipantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessagingController extends Controller
{
    public function __construct(
        protected MessagingService $messagingService
    ) {}

    /**
     * GET /tenant/messages/conversations
     * List all conversations for the authenticated tenant user with students
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = $this->messagingService->getConversations(
            ParticipantType::TENANT,
            $user->id,
            $request->input('per_page', 15)
        );

        return response()->json($conversations);
    }

    /**
     * GET /tenant/messages/conversations/{id}
     * Get a specific conversation with messages
     */
    public function show(Request $request, int $id)
    {
        $conversation = Conversation::findOrFail($id);

        Gate::authorize('view', $conversation);

        $messages = $this->messagingService->getMessages(
            $id,
            $request->input('per_page', 50)
        );

        return response()->json([
            'conversation' => $conversation->load(['participants', 'lastMessage', 'student']),
            'messages' => $messages,
        ]);
    }

    /**
     * POST /tenant/messages/conversations
     * Create or get conversation with a student
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Conversation::class);

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject' => 'nullable|string|max:255',
        ]);

        $conversation = $this->messagingService->findOrCreateConversation(
            $request->student_id,
            $request->subject
        );

        return response()->json($conversation->load(['participants', 'student']), 201);
    }

    /**
     * POST /tenant/messages/conversations/{id}/messages
     * Send a message in a conversation
     */
    public function sendMessage(Request $request, int $id)
    {
        $conversation = Conversation::findOrFail($id);

        Gate::authorize('sendMessage', $conversation);

        $request->validate([
            'body' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*.path' => 'required|string',
            'attachments.*.name' => 'required|string',
            'attachments.*.mime' => 'required|string',
            'attachments.*.size' => 'required|integer',
        ]);

        $message = $this->messagingService->sendMessage(
            $id,
            ParticipantType::TENANT,
            $request->user()->id,
            $request->body,
            $request->attachments
        );

        return response()->json($message->load('conversation'), 201);
    }

    /**
     * POST /tenant/messages/conversations/{id}/read
     * Mark conversation as read
     */
    public function markAsRead(Request $request, int $id)
    {
        $conversation = Conversation::findOrFail($id);

        Gate::authorize('markAsRead', $conversation);

        $this->messagingService->markAsRead(
            $id,
            ParticipantType::TENANT,
            $request->user()->id
        );

        return response()->json(['message' => 'Marked as read']);
    }

    /**
     * POST /tenant/messages/conversations/{id}/mute
     * Toggle mute status
     */
    public function toggleMute(Request $request, int $id)
    {
        $conversation = Conversation::findOrFail($id);

        Gate::authorize('toggleMute', $conversation);

        $request->validate([
            'mute' => 'required|boolean',
        ]);

        $this->messagingService->toggleMute(
            $id,
            ParticipantType::TENANT,
            $request->user()->id,
            $request->mute
        );

        return response()->json(['message' => 'Mute status updated']);
    }

    /**
     * GET /tenant/messages/unread-count
     * Get unread messages count
     */
    public function unreadCount(Request $request)
    {
        $count = $this->messagingService->getUnreadCount(
            ParticipantType::TENANT,
            $request->user()->id
        );

        return response()->json(['count' => $count]);
    }

    /**
     * DELETE /tenant/messages/conversations/{id}
     * Delete a conversation
     */
    public function destroy(Request $request, int $id)
    {
        $conversation = Conversation::findOrFail($id);

        Gate::authorize('delete', $conversation);

        $this->messagingService->deleteConversation($id);

        return response()->json(['message' => 'Conversation deleted']);
    }
}
