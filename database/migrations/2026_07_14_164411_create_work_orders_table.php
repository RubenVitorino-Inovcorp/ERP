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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('entity_id')->constrained('entities'); // Cliente
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); // Encomenda origem
            $table->foreignId('calendar_event_id')->nullable()->constrained('calendar_events')->nullOnDelete(); // Agendamento
            $table->text('description'); // Descrição do trabalho
            $table->string('priority')->default('normal'); // baixa, normal, alta, urgente
            $table->string('status')->default('pendente'); // pendente, em_curso, concluida, cancelada
            $table->integer('expected_duration')->nullable()->comment('Expected duration in minutes'); // Tempo Previsto
            $table->integer('actual_duration')->nullable()->comment('Actual duration in minutes'); // Tempo Gasto
            $table->text('notes')->nullable(); // Notas/Relatório Técnico
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
