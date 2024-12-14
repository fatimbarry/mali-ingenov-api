<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('taches', function (Blueprint $table) {
            // Colonnes pour les utilisateurs assignés
            $table->unsignedBigInteger('assigned_to')->nullable()->after('id'); // ID de l'utilisateur assigné
            $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_to'); // ID de l'utilisateur qui assigne

            // Colonne pour la date d'échéance
            $table->date('due_date')->nullable()->after('assigned_by'); // Date limite pour la tâche

            // Ajout des clés étrangères si nécessaire
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['assigned_to', 'assigned_by', 'due_date']);
        });
    }
};
