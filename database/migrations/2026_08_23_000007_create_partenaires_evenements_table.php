<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table pivot utilisée par Evenement::partenaires() et Partenaire::evenements()
 * (belongsToMany dans les deux sens).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('partenaires_evenements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evenement_id')->constrained('evenements')->cascadeOnDelete();
            $table->foreignId('partenaire_id')->constrained('partenaires')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['evenement_id', 'partenaire_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('partenaires_evenements');
    }
};
