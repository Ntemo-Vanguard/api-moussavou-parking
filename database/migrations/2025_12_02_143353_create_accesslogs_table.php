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
        Schema::create('accesslogs', function (Blueprint $table) {
            $table->id();
            $table->enum('statut', ['accepte', 'refuse']);
            $table->string('raison')->nullable();
            $table->foreignId('carte_id')->constrained('cartes')->onDelete('cascade');
            $table->foreignId('parking_id')->nullable()->constrained('parkings')->onDelete('set null');
            $table->timestamp('date_acces')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesslogs');
    }
};
