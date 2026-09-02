<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renseignés uniquement quand `objet = 'partenariat'` (formulaire de
     * contact public) — ils permettent à l'admin de créer la fiche
     * Partenaire en un clic depuis le message, sans tout retaper.
     * `partenaire_id` trace la conversion pour ne pas dupliquer la fiche.
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('organisation')->nullable()->after('telephone');
            $table->enum('type_organisation', ['institution', 'ong', 'entreprise', 'media', 'universite', 'association'])
                ->nullable()->after('organisation');
            $table->string('secteur_activite')->nullable()->after('type_organisation');
            $table->string('pays')->nullable()->after('secteur_activite');
            $table->string('ville')->nullable()->after('pays');
            $table->string('site_web')->nullable()->after('ville');

            $table->foreignId('partenaire_id')->nullable()->after('traite_par')
                ->constrained('partenaires')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partenaire_id');
            $table->dropColumn(['organisation', 'type_organisation', 'secteur_activite', 'pays', 'ville', 'site_web']);
        });
    }
};
