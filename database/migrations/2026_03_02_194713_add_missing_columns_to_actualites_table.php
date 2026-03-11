<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('actualites', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            $table->string('slug')->unique()->after('titre');
            $table->string('categorie')->nullable()->after('type');
            $table->datetime('date_publicacion')->nullable()->after('type'); // ou utilisez date_publication
            $table->string('source')->nullable()->after('auteur');
            $table->integer('vues')->default(0)->after('source');
            $table->json('tags')->nullable()->after('vues');
            $table->boolean('est_a_la_une')->default(false)->after('tags');
            
            // Note: date_publication n'existe pas, vous avez date_evenement
            // Si vous voulez date_publication, renommez d'abord :
            // $table->renameColumn('date_evenement', 'date_publication');
        });
    }

    public function down()
    {
        Schema::table('actualites', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'categorie',
                'source',
                'vues',
                'tags',
                'est_a_la_une'
            ]);
        });
    }
};