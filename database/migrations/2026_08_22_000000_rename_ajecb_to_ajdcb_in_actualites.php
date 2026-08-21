<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * L'association a été renommée AJECB -> AJDCB. La migration de création de
 * 'actualites' (2026_02_27_084834) a probablement déjà tourné sur les bases
 * existantes, donc corriger son fichier ne change rien en prod : on met ici
 * à jour les lignes déjà enregistrées avec l'ancien nom.
 *
 * Note : le défaut de colonne ('auteur' => 'AJECB' au niveau du schéma) n'est
 * pas modifié ici car cela nécessite le package doctrine/dbal (absent de ce
 * projet). Sans incidence pratique tant que le contrôleur fixe explicitement
 * 'auteur' => 'AJDCB' (voir ActualiteController::store), ce qu'il fait déjà.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('actualites')->where('auteur', 'AJECB')->update(['auteur' => 'AJDCB']);
    }

    public function down(): void
    {
        DB::table('actualites')->where('auteur', 'AJDCB')->update(['auteur' => 'AJECB']);
    }
};
