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
        Schema::create('current_account_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('document_type'); // e.g., 'Fatura', 'Recibo', 'Nota de Crédito', 'Saldo Inicial', 'Outro'
            $table->string('document_number')->nullable();
            $table->string('description')->nullable();
            $table->enum('movement_type', ['debit', 'credit']); // debit = debt goes up, credit = debt goes down
            $table->decimal('amount', 10, 2);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_account_entries');
    }
};
