<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->enum('status', ['en cours', 'terminé', 'validé', 'À faire'])
                ->change();
        });
    }

    public function down()
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->enum('status', ['en cours', 'terminé', 'validé', 'À faire'])
                ->change();
        });
    }
};
