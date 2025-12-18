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
            $table->id();
            $table->decimal('montant', 10, 2);
            $table->enum('type', ['recharge', 'paiement_parking']);
            $table->enum('moyen', ['orange_money', 'wave', 'free_money', 'cash'])->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'echoue'])->default('en_attente');
            $table->foreignId('carte_id')->constrained('cartes')->onDelete('cascade');
            // Utilisateur qui a initié l'opération (normalement un client)
            $table->foreignId('utilisateur_id')->constrained('personnes')->onDelete('cascade');
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
