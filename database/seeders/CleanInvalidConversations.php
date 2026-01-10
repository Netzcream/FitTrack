<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Central\Conversation;

class CleanInvalidConversations extends Seeder
{
    public function run(): void
    {
        // Eliminar conversaciones con tenant_id inválido ('0')
        Conversation::where('tenant_id', '0')->delete();
    }
}
