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
        Schema::create('taches', function (Blueprint $table) {
            $table->id(); // ID unique pour chaque tâche
            $table->string('titre'); // Titre de la tâche
            $table->text('description')->nullable(); // Description détaillée de la tâche
            $table->time('temps_previs')->nullable(); // Temps prévu pour accomplir la tâche
            $table->enum('status', ['en cours', 'terminé', 'validé'])->default('en cours');
            $table->foreignId('projet_id')->constrained('projets')->onDelete('cascade'); // Clé étrangère vers la table projets
            $table->timestamps(); // Champs created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
