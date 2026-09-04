<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bug pré-existant : `App\Models\Message` référence `traite_par` (fillable,
     * relation `traitePar()`, `MessageController::marquerTraite()`) depuis le
     * tout premier commit du repo, mais aucune migration n'a jamais créé cette
     * colonne sur `messages` (elle n'existe que sur `adhesions`, cf.
     * 2026_08_25_000001_extend_adhesions_table.php). Ça n'avait jamais planté
     * faute d'avoir déclenché un chemin de code qui l'utilise vraiment, jusqu'à
     * ce que la migration 2026_09_02_020000 tente un ->after('traite_par').
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'traite_par')) {
                $table->foreignId('traite_par')->nullable()->after('statut')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'traite_par')) {
                $table->dropConstrainedForeignId('traite_par');
            }
        });
    }
};
