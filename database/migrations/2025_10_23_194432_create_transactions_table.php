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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id');
            $table->decimal('montant', 15, 2);
            $table->enum('type', ['depot', 'retrait', 'virement', 'frais']);
            $table->string('devise', 10)->default('FCFA');
            $table->string('description')->nullable();
            $table->enum('status', ['en_attente', 'validee', 'annulee'])->default('en_attente');
            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
