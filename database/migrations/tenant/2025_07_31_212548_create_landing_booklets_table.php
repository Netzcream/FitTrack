<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landing_booklets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('text')->nullable();
            $table->string('link')->nullable();
            $table->string('target')->default('_self');
            $table->boolean('active')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_booklets');
    }
};
