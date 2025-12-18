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
        Schema::create('cartes', function (Blueprint $table) {
            $table->id();
            $table->string('code_rfid')->unique();
            $table->decimal('solde', 10, 2)->default(0);
            $table->enum('statut', ['active', 'bloquee'])->default('active');
            // Seuls les clients ont une carte, mais on pointe vers personnes.id
            $table->foreignId('utilisateur_id')->constrained('personnes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartes');
    }
};
