<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('actualites', function (Blueprint $table) {
            // Lien du post une fois publié sur la Page Facebook — sert aussi de
            // marqueur "déjà partagé" pour éviter les doublons depuis l'admin.
            $table->string('facebook_post_url')->nullable()->after('statut');
        });
    }

    public function down()
    {
        Schema::table('actualites', function (Blueprint $table) {
            $table->dropColumn('facebook_post_url');
        });
    }
};
