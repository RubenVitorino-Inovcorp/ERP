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
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->foreignId('entity_id')->constrained('entities');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->decimal('total_value', 10, 2);
            $table->string('document_path')->nullable();
            $table->string('proof_of_payment_path')->nullable();
            $table->enum('status', ['pendente', 'paga'])->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
