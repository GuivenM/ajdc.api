<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('journal_activites', function (Blueprint $table) {
            $table->id();
            // Nullable : une action automatique (cron, ex. radiation auto) n'a
            // pas d'utilisateur derrière.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Identifiant court de l'action, ex. 'cotisation.marquer',
            // 'membre.supprimer' — utile pour filtrer plus tard.
            $table->string('action');
            // Résumé lisible, déjà formaté, affiché tel quel dans l'admin —
            // on ne recalcule rien à l'affichage.
            $table->text('description');
            // Sur quoi portait l'action (poly-morphique léger, sans les
            // contraintes FK d'un vrai morphTo pour rester simple).
            $table->string('sujet_type')->nullable();
            $table->unsignedBigInteger('sujet_id')->nullable();
            // Contexte structuré additionnel (ex. anciennes/nouvelles valeurs).
            $table->json('donnees')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sujet_type', 'sujet_id']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('journal_activites');
    }
};
