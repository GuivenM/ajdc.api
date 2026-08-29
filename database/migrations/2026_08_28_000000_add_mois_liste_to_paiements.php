<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('paiements', function (Blueprint $table) {
            // Liste complète des mois couverts par ce paiement (ex: ["2026-06","2026-07"]).
            // `mois` reste renseigné avec le premier mois de la liste, pour compat/affichage.
            $table->json('mois_liste')->nullable()->after('mois');
        });
    }

    public function down()
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropColumn('mois_liste');
        });
    }
};
