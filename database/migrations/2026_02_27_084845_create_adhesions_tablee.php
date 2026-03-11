<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('adhesions', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('nationalite')->default('Congolaise');
            $table->string('email')->unique();
            $table->string('telephone');
            $table->string('adresse');
            $table->string('ville');
            $table->string('profession');
            $table->string('niveau_etude');
            $table->text('motivation');
            $table->enum('statut', ['en_attente', 'approuvee', 'rejetee'])->default('en_attente');
            $table->datetime('date_traitement')->nullable();
            $table->text('commentaire_traitement')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adhesions');
    }
};