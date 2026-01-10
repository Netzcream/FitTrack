<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Tenant\MessagingService;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Student;
use App\Enums\ParticipantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessagingController extends Controller
{
    public function __construct(
        protected MessagingService $messagingService
    ) {}

    /**
     * GET /api/messages/conversation
     * Get student's conversation with trainer
     */
    public function show(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $conversation = $this->messagingService->getConversationForStudent($student->id);

        if (!$conversation) {
            // Auto-create conversation if doesn't exist
            $conversation = $this->messagingService->findOrCreateConversation($student->id);
        }

        Gate::authorize('viewAsStudent', [$conversation, $student]);

        $messages = $this->messagingService->getMessages(
            $conversation->id,
            $request->input('per_page', 50)
        );

        return response()->json([
            'conversation' => $conversation->load(['participants', 'lastMessage']),
            'messages' => $messages,
        ]);
    }

    /**
     * POST /api/messages/send
     * Send a message to trainer
     */
    public function sendMessage(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $conversation = $this->messagingService->getConversationForStudent($student->id);

        if (!$conversation) {
            $conversation = $this->messagingService->findOrCreateConversation($student->id);
        }

        Gate::authorize('sendMessageAsStudent', [$conversation, $student]);

        $request->validate([
            'body' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*.path' => 'required|string',
            'attachments.*.name' => 'required|string',
            'attachments.*.mime' => 'required|string',
            'attachments.*.size' => 'required|integer',
        ]);

        $message = $this->messagingService->sendMessage(
            $conversation->id,
            ParticipantType::STUDENT,
            $student->id,
            $request->body,
            $request->attachments
        );

        return response()->json($message, 201);
    }

    /**
     * POST /api/messages/read
     * Mark conversation as read
     */
    public function markAsRead(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $conversation = $this->messagingService->getConversationForStudent($student->id);

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        Gate::authorize('markAsReadAsStudent', [$conversation, $student]);

        $this->messagingService->markAsRead(
            $conversation->id,
            ParticipantType::STUDENT,
            $student->id
        );

        return response()->json(['message' => 'Marked as read']);
    }

    /**
     * GET /api/messages/unread-count
     * Get unread messages count
     */
    public function unreadCount(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $count = $this->messagingService->getUnreadCount(
            ParticipantType::STUDENT,
            $student->id
        );

        return response()->json(['count' => $count]);
    }

    /**
     * POST /api/messages/mute
     * Toggle mute status
     */
    public function toggleMute(Request $request)
    {
        $student = Student::where('email', $request->user()->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $conversation = $this->messagingService->getConversationForStudent($student->id);

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        Gate::authorize('toggleMuteAsStudent', [$conversation, $student]);

        $request->validate([
            'mute' => 'required|boolean',
        ]);

        $this->messagingService->toggleMute(
            $conversation->id,
            ParticipantType::STUDENT,
            $student->id,
            $request->mute
        );

        return response()->json(['message' => 'Mute status updated']);
    }
}
