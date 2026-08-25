<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cotisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->constrained('membres')->cascadeOnDelete();
            $table->char('mois', 7); // format 'YYYY-MM'
            $table->decimal('montant', 10, 2)->default(1000);
            $table->date('date_paiement')->nullable();
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'virement', 'autre'])->nullable();
            $table->enum('statut', ['payee', 'impayee'])->default('impayee');
            $table->text('commentaire')->nullable();
            $table->foreignId('enregistre_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['membre_id', 'mois']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotisations');
    }
};
