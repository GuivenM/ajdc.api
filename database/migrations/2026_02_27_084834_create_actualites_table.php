<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actualites', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->longText('contenu');
            $table->string('image');
            $table->enum('type', ['actualite', 'evenement', 'education', 'culture']);
            $table->datetime('date_evenement')->nullable();
            $table->string('lieu_evenement')->nullable();
            $table->string('auteur')->default('AJECB');
            $table->enum('statut', ['publie', 'brouillon'])->default('publie');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('actualites');
    }
};