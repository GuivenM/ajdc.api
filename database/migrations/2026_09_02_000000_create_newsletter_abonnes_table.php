<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('newsletter_abonnes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->enum('statut', ['actif', 'desinscrit'])->default('actif');
            // D'où vient l'inscription (footer, page guide, etc.) — utile pour analyser
            // quelle partie du site convertit le mieux, sans conséquence fonctionnelle.
            $table->string('source')->nullable();
            $table->timestamp('desinscrit_le')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('newsletter_abonnes');
    }
};
