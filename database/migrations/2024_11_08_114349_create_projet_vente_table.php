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
        Schema::create('projet_vente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')
                ->constrained('projets')
                ->onDelete('cascade');
            $table->foreignId('vente_id')
                ->constrained('ventes')
                ->onDelete('cascade');
            $table->decimal('prix_unitaire', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projet_vente');
    }
};
