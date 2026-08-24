<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table pivot utilisée par Evenement::participants() (belongsToMany vers
 * Membre, avec les colonnes pivot statut/date_inscription/commentaire).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('participations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evenement_id')->constrained('evenements')->cascadeOnDelete();
            $table->foreignId('membre_id')->constrained('membres')->cascadeOnDelete();
            $table->enum('statut', ['inscrit', 'confirme', 'present', 'absent', 'annule'])->default('inscrit');
            $table->timestamp('date_inscription')->useCurrent();
            $table->text('commentaire')->nullable();

            $table->timestamps();

            $table->unique(['evenement_id', 'membre_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('participations');
    }
};
