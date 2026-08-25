<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();

            // 'cotisation' ou 'evenement'
            $table->string('type');

            $table->foreignId('membre_id')->nullable()->constrained('membres')->nullOnDelete();
            $table->foreignId('evenement_id')->nullable()->constrained('evenements')->nullOnDelete();

            // Renseigné uniquement pour une cotisation (format AAAA-MM)
            $table->string('mois')->nullable();

            // Coordonnées du payeur, utiles quand aucun membre_id n'est fourni
            // (ex: achat de billet par un non-membre) et pour contacter FedaPay.
            $table->string('nom_payeur')->nullable();
            $table->string('telephone_payeur')->nullable();
            $table->string('email_payeur')->nullable();

            $table->decimal('montant', 10, 2);
            $table->string('devise', 10)->default('XOF');

            // en_attente -> reussi | echoue | annule
            $table->string('statut')->default('en_attente');

            $table->string('fedapay_transaction_id')->nullable()->unique();
            $table->string('fedapay_reference')->nullable();
            $table->text('fedapay_derniere_reponse')->nullable();

            $table->timestamps();

            $table->index(['type', 'statut']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('paiements');
    }
};
