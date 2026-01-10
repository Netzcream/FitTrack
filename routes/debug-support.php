<?php

// Quick test route to debug the Conversation model and what's being passed
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/debug-support', function () {
        $conversations = \App\Models\Central\Conversation::has('messages')
            ->whereHas('tenant')
            ->orderByDesc('last_message_at')
            ->limit(5)
            ->get();

        dd([
            'count' => $conversations->count(),
            'items' => $conversations->map(fn($c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'tenant_id' => $c->tenant_id,
                'tenant' => $c->tenant ? $c->tenant->toArray() : null,
                'tenant_name_type' => gettype($c->tenant?->name),
                'tenant_name' => $c->tenant?->name,
                'last_message_at' => $c->last_message_at,
                'route_url' => route('support.show', $c),
            ])->toArray(),
        ]);
    });
});
