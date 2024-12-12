<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();

            // Type de contrat avec enum
            $table->enum('type_contrat', ['Service', 'Maintenance', 'Consultation', 'Développement']);

            // Statut du contrat avec enum
            $table->enum('statut', ['Actif', 'En attente', 'Terminé', 'Annulé'])->default('En attente');

            $table->decimal('montant', 15, 2); // Exemple de montant avec précision
            $table->date('date');
            $table->unsignedBigInteger('projet_id');
            $table->timestamps();

            // Clé étrangère
            $table->foreign('projet_id')->references('id')->on('projets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contrats');
    }

};
