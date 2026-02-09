<?php

namespace App\Policies\Tenant;

use App\Models\User;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Student;
use App\Enums\ConversationType;

class ConversationPolicy
{
    /**
     * Determine if the user can view the conversation
     */
    public function view(User $user, Conversation $conversation): bool
    {
        // Only tenant_student conversations in tenant context
        return $conversation->type === ConversationType::TENANT_STUDENT;
    }

    /**
     * Determine if the student can view the conversation
     */
    public function viewAsStudent(?Student $student, Conversation $conversation): bool
    {
        if (!$student) {
            return false;
        }

        // Student can only view their own conversation
        return $conversation->student_id === $student->id
            && $conversation->type === ConversationType::TENANT_STUDENT;
    }

    /**
     * Determine if the user can send messages in the conversation
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the student can send messages
     */
    public function sendMessageAsStudent(?Student $student, Conversation $conversation): bool
    {
        return $this->viewAsStudent($student, $conversation);
    }

    /**
     * Determine if the user can mark the conversation as read
     */
    public function markAsRead(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the student can mark as read
     */
    public function markAsReadAsStudent(?Student $student, Conversation $conversation): bool
    {
        return $this->viewAsStudent($student, $conversation);
    }

    /**
     * Determine if the user can delete the conversation
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        // Only tenant admins can delete conversations
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can mute/unmute the conversation
     */
    public function toggleMute(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the student can mute/unmute
     */
    public function toggleMuteAsStudent(?Student $student, Conversation $conversation): bool
    {
        return $this->viewAsStudent($student, $conversation);
    }

    /**
     * Determine if the user can create a conversation with a student
     */
    public function create(User $user): bool
    {
        // All authenticated tenant users can create conversations with students
        return true;
    }

    /**
     * Determine if the student can create a conversation (they get auto-created)
     */
    public function createAsStudent(?Student $student): bool
    {
        return $student !== null;
    }
}
