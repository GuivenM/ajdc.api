<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('membres', function (Blueprint $table) {
            $table->foreignId('adhesion_id')->nullable()->unique()
                ->after('id')
                ->constrained('adhesions')->nullOnDelete();
        });

        // Un membre créé à partir d'une adhésion approuvée démarre "en attente
        // de paiement" (cotisation initiale de 1000F) avant de passer "actif".
        DB::statement("ALTER TABLE membres MODIFY statut ENUM('actif', 'inactif', 'en_attente_paiement') NOT NULL DEFAULT 'actif'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE membres MODIFY statut ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif'");

        Schema::table('membres', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adhesion_id');
        });
    }
};
