<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina la tabla payment_methods ya que se migró a configuraciones.
     */
    public function up(): void
    {
        // Primero eliminar la foreign key y columna de la tabla payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });

        // Ahora podemos eliminar la tabla payment_methods
        Schema::dropIfExists('payment_methods');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear la tabla payment_methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Recrear la columna y foreign key en payments
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('student_id')->constrained('payment_methods')->nullOnDelete();
        });
    }
};
