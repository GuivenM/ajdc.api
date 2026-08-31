<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute à `membres` ce qu'il faut pour que le modèle devienne
     * authentifiable (espace membre, distinct de l'espace admin qui
     * repose sur `users`). L'email est copié depuis `adhesions.email`
     * au moment de l'approbation (voir AdhesionController::traiter) ;
     * le mot de passe n'est défini qu'à l'activation du compte.
     */
    public function up()
    {
        Schema::table('membres', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('adhesion_id');
            $table->string('password')->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->timestamp('derniere_connexion')->nullable()->after('email_verified_at');

            // Activation du compte (lien envoyé par email à l'approbation
            // de l'adhésion). Le membre définit son mot de passe via ce
            // token, à durée de vie limitée.
            $table->string('activation_token')->nullable()->unique()->after('derniere_connexion');
            $table->timestamp('activation_token_expire_at')->nullable()->after('activation_token');
        });
    }

    public function down()
    {
        Schema::table('membres', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'password',
                'email_verified_at',
                'derniere_connexion',
                'activation_token',
                'activation_token_expire_at',
            ]);
        });
    }
};
