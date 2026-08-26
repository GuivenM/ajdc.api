<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('adhesions', function (Blueprint $table) {
            // --- Colonnes déjà référencées par le modèle mais absentes de la
            // migration d'origine (bug pré-existant : ces champs ne pouvaient
            // pas être enregistrés) ---
            $table->string('etablissement')->nullable()->after('niveau_etude');
            $table->json('competences')->nullable()->after('motivation');
            $table->json('centres_interet')->nullable()->after('competences');
            $table->string('disponibilite')->nullable()->after('centres_interet');
            $table->text('commentaire')->nullable()->after('disponibilite');
            $table->foreignId('traite_par')->nullable()->after('commentaire_traitement')
                ->constrained('users')->nullOnDelete();

            // --- Nationalité & pièces ---
            $table->boolean('est_congolais')->default(true)->after('nationalite');
            $table->boolean('possede_carte_consulaire')->nullable()->after('est_congolais');
            $table->string('carte_consulaire_fichier')->nullable()->after('possede_carte_consulaire');
            $table->string('duree_au_benin')->nullable()->after('carte_consulaire_fichier');
            $table->boolean('possede_cipr')->nullable()->after('duree_au_benin');
            $table->string('cipr_fichier')->nullable()->after('possede_cipr');

            // --- État civil ---
            $table->string('nom_marital')->nullable()->after('prenom');
            $table->enum('sexe', ['masculin', 'feminin'])->nullable()->after('nom_marital');
            $table->enum('situation_matrimoniale', ['marie', 'divorce', 'union_libre', 'celibataire', 'veuf'])
                ->nullable()->after('adresse');
            $table->unsignedInteger('nombre_enfants_charge')->nullable()->default(0)->after('situation_matrimoniale');

            // --- Photo (badge) ---
            $table->string('photo')->nullable()->after('cipr_fichier');

            // --- Statut professionnel ---
            $table->string('profession_autre')->nullable()->after('profession');
            $table->string('niveau_etude_autre')->nullable()->after('niveau_etude');
            $table->string('dernier_diplome')->nullable()->after('niveau_etude_autre');
            $table->string('dernier_diplome_autre')->nullable()->after('dernier_diplome');

            // --- Entrepreneur ---
            $table->string('entrepreneur_domaine')->nullable()->after('etablissement');
            $table->string('entrepreneur_domaine_autre')->nullable()->after('entrepreneur_domaine');
            $table->string('entrepreneur_duree')->nullable()->after('entrepreneur_domaine_autre');
            $table->string('entrepreneur_nom_entreprise')->nullable()->after('entrepreneur_duree');
            $table->string('entrepreneur_fonction')->nullable()->after('entrepreneur_nom_entreprise');

            // --- Étudiant ---
            $table->string('etudiant_filiere')->nullable()->after('entrepreneur_fonction');
            $table->string('etudiant_annee')->nullable()->after('etudiant_filiere');

            // --- Compétences, intérêts, langues ---
            $table->string('competences_autre')->nullable()->after('competences');
            $table->string('domaines_interet_autre')->nullable()->after('centres_interet');
            $table->json('loisirs')->nullable()->after('domaines_interet_autre');
            $table->string('loisirs_autre')->nullable()->after('loisirs');
            $table->json('langues')->nullable()->after('loisirs_autre');

            // --- Engagement associatif ---
            $table->string('comment_connu')->nullable()->after('langues');
            $table->string('comment_connu_autre')->nullable()->after('comment_connu');
            $table->string('recommande_par')->nullable()->after('comment_connu_autre');
            $table->boolean('experience_associative')->nullable()->after('recommande_par');
            $table->text('experience_associative_details')->nullable()->after('experience_associative');
            $table->json('commissions_souhaitees')->nullable()->after('experience_associative_details');
            $table->text('attentes')->nullable()->after('commissions_souhaitees');

            // --- Coordonnées ---
            $table->string('autre_telephone')->nullable()->after('telephone');

            // --- Déclaration sur l'honneur ---
            $table->string('declarant_nom_complet')->nullable()->after('attentes');
            $table->boolean('accepte_conditions')->default(false)->after('declarant_nom_complet');
            $table->boolean('souhaite_recevoir_actualites')->nullable()->after('accepte_conditions');
            $table->json('lettre_demande_fichiers')->nullable()->after('souhaite_recevoir_actualites');
        });
    }

    public function down()
    {
        Schema::table('adhesions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('traite_par');
            $table->dropColumn([
                'etablissement', 'competences', 'centres_interet', 'disponibilite', 'commentaire',
                'est_congolais', 'possede_carte_consulaire', 'carte_consulaire_fichier', 'duree_au_benin',
                'possede_cipr', 'cipr_fichier', 'nom_marital', 'sexe', 'situation_matrimoniale',
                'nombre_enfants_charge', 'photo', 'profession_autre', 'niveau_etude_autre',
                'dernier_diplome', 'dernier_diplome_autre', 'entrepreneur_domaine',
                'entrepreneur_domaine_autre', 'entrepreneur_duree', 'entrepreneur_nom_entreprise',
                'entrepreneur_fonction', 'etudiant_filiere', 'etudiant_annee', 'competences_autre',
                'domaines_interet_autre', 'loisirs', 'loisirs_autre', 'langues', 'comment_connu',
                'comment_connu_autre', 'recommande_par', 'experience_associative',
                'experience_associative_details', 'commissions_souhaitees', 'attentes',
                'autre_telephone', 'declarant_nom_complet', 'accepte_conditions',
                'souhaite_recevoir_actualites', 'lettre_demande_fichiers',
            ]);
        });
    }
};