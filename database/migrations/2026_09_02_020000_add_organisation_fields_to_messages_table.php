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
            if (!Schema::hasColumn('messages', 'organisation')) {
                $table->string('organisation')->nullable()->after('telephone');
            }
            if (!Schema::hasColumn('messages', 'type_organisation')) {
                $table->enum('type_organisation', ['institution', 'ong', 'entreprise', 'media', 'universite', 'association'])
                    ->nullable()->after('organisation');
            }
            if (!Schema::hasColumn('messages', 'secteur_activite')) {
                $table->string('secteur_activite')->nullable()->after('type_organisation');
            }
            if (!Schema::hasColumn('messages', 'pays')) {
                $table->string('pays')->nullable()->after('secteur_activite');
            }
            if (!Schema::hasColumn('messages', 'ville')) {
                $table->string('ville')->nullable()->after('pays');
            }
            if (!Schema::hasColumn('messages', 'site_web')) {
                $table->string('site_web')->nullable()->after('ville');
            }
            if (!Schema::hasColumn('messages', 'partenaire_id')) {
                $table->foreignId('partenaire_id')->nullable()->after('traite_par')
                    ->constrained('partenaires')->nullOnDelete();
            }
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
