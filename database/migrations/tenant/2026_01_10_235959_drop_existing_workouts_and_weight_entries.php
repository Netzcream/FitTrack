<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('student_weight_entries');
        Schema::dropIfExists('workouts');
    }

    public function down(): void
    {
        // Migrations will recreate them
    }
};
