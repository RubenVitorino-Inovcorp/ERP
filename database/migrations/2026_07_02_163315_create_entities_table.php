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
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['cliente', 'fornecedor', 'ambos'])->default('cliente');
            $table->unsignedBigInteger('number')->unique();
            $table->text('nif');
            $table->string('name');
            $table->string('address');
            $table->string('zip_code');
            $table->string('city');
            $table->foreignId('country_id')->constrained('countries');
            $table->text('phone')->nullable();
            $table->text('mobile')->nullable();
            $table->string('website')->nullable();
            $table->text('email');
            $table->boolean('gdpr_consent')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
