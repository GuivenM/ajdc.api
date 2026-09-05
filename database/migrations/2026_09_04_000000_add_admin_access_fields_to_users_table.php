<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'membre_id')) {
                // Le compte admin a-t-il été créé depuis une fiche Membre (bureau) ?
                // Nullable : les comptes créés à la main (ex. premier super_admin) n'ont pas de membre lié.
                $table->foreignId('membre_id')->nullable()->after('id')
                    ->constrained('membres')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'activation_token')) {
                $table->string('activation_token')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'activation_token_expire_at')) {
                $table->timestamp('activation_token_expire_at')->nullable()->after('activation_token');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'membre_id')) {
                $table->dropConstrainedForeignId('membre_id');
            }
            if (Schema::hasColumn('users', 'activation_token_expire_at')) {
                $table->dropColumn('activation_token_expire_at');
            }
            if (Schema::hasColumn('users', 'activation_token')) {
                $table->dropColumn('activation_token');
            }
        });
    }
};
