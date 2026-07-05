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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guard_id')->constrained('guards')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['Radio', 'Fax', 'Correo Electrónico'])->default('Radio');
            $table->enum('direction', ['Recibido', 'Expedido'])->default('Recibido');
            $table->string('number');
            $table->time('time')->nullable();
            $table->string('office')->nullable();
            $table->string('affair')->nullable();
            $table->text('text');
            $table->enum('clasification', ['Rutinario','Prioritario', 'Urgente', 'Destello'])->default('Rutinario');
            $table->boolean('confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
