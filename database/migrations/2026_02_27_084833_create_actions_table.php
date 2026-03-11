<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('titre');
            $table->text('description'); // Description générale
            $table->enum('section', ['solidarite', 'education', 'culture', 'communication']);
            
            // Image principale de l'action
            $table->string('image')->nullable();
            
            // Dates importantes
            $table->date('date_debut')->nullable();    // Date de début de l'action
            $table->date('date_fin')->nullable();      // Date de fin de l'action
            $table->date('date_evenement')->nullable(); // Date spécifique pour un événement
            
            // Lieu (optionnel)
            $table->string('lieu')->nullable();
            
            // Détails supplémentaires
            $table->json('objectifs')->nullable();      // Liste des objectifs
            $table->json('activites_cles')->nullable(); // Liste des activités clés
            $table->json('resultats')->nullable();      // Liste des résultats attendus/obtenus
            
            // Informations de publication
            $table->enum('statut', ['actif', 'inactif', 'a_venir', 'termine'])->default('actif');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('actions');
    }
};