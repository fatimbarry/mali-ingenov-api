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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture')->unique();
            $table->unsignedBigInteger('client_id');
            $table->date('date_facture'); // Date de la facture
            $table->decimal('montant_total', 10, 2); // Montant total de la facture
            $table->timestamps();
            $table->unsignedBigInteger('vente_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('vente_id')->references('id')->on('ventes')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
