<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `image` était NOT NULL sans valeur par défaut alors que la création
     * d'actualité passe maintenant exclusivement par la galerie `photos[]`
     * (voir ActualiteController::store, Actualite::getImageUrlAttribute).
     * Le champ n'est plus jamais envoyé par le formulaire → l'insert
     * échouait systématiquement (SQLSTATE[HY000]: 1364).
     */
    public function up()
    {
        Schema::table('actualites', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('actualites', function (Blueprint $table) {
            $table->string('image')->nullable(false)->change();
        });
    }
};
