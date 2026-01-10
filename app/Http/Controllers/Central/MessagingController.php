<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Central\MessagingService;
use App\Models\Central\Conversation;
use App\Enums\ParticipantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessagingController extends Controller
{
    public function __construct(
        protected MessagingService $messagingService
    ) {}

    /**
     * GET /central/messages/conversations
     * List all conversations for the authenticated central user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = $this->messagingService->getConversations(
            ParticipantType::CENTRAL,
            $user->id,
            $request->input('per_page', 15)
        );

        return response()->json($conversations);
    }

    /**
     * GET /central/messages/conversations/{id}
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
            'conversation' => $conversation->load(['participants', 'lastMessage']),
            'messages' => $messages,
        ]);
    }

    /**
     * POST /central/messages/conversations
     * Create or get conversation with a tenant
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Conversation::class);

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subject' => 'nullable|string|max:255',
        ]);

        $conversation = $this->messagingService->findOrCreateConversation(
            $request->tenant_id,
            $request->subject
        );

        return response()->json($conversation->load('participants'), 201);
    }

    /**
     * POST /central/messages/conversations/{id}/messages
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
            ParticipantType::CENTRAL,
            $request->user()->id,
            $request->body,
            $request->attachments
        );

        return response()->json($message->load('conversation'), 201);
    }

    /**
     * POST /central/messages/conversations/{id}/read
     * Mark conversation as read
     */
    public function markAsRead(Request $request, int $id)
    {
        $conversation = Conversation::findOrFail($id);

        Gate::authorize('markAsRead', $conversation);

        $this->messagingService->markAsRead(
            $id,
            ParticipantType::CENTRAL,
            $request->user()->id
        );

        return response()->json(['message' => 'Marked as read']);
    }

    /**
     * POST /central/messages/conversations/{id}/mute
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
            ParticipantType::CENTRAL,
            $request->user()->id,
            $request->mute
        );

        return response()->json(['message' => 'Mute status updated']);
    }

    /**
     * GET /central/messages/unread-count
     * Get unread messages count
     */
    public function unreadCount(Request $request)
    {
        $count = $this->messagingService->getUnreadCount(
            ParticipantType::CENTRAL,
            $request->user()->id
        );

        return response()->json(['count' => $count]);
    }

    /**
     * DELETE /central/messages/conversations/{id}
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
