<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id(); // ID unique pour chaque pointage
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade'); // Clé étrangère vers la table employeur
            $table->enum('status', ['present', 'absent'])->default('absent'); // Status de présence
            $table->date('date'); // Date du pointage
            $table->time('heure_normales')->nullable(); // Heures normales de travail
            $table->time('heure_supplementaires')->nullable(); // Heures supplémentaires
            $table->string('heure_pointage'); // Heure d'entrée et de sortie, stockée comme chaîne pour les deux valeurs
            $table->time('ecart')->nullable(); // Différence entre le temps travaillé et le temps attendu

            $table->timestamps(); // Champs created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};
