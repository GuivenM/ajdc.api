<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('membres', function (Blueprint $table) {
            if (!Schema::hasColumn('membres', 'motif_inactivation')) {
                $table->text('motif_inactivation')->nullable()->after('statut');
            }
        });
    }

    public function down()
    {
        Schema::table('membres', function (Blueprint $table) {
            if (Schema::hasColumn('membres', 'motif_inactivation')) {
                $table->dropColumn('motif_inactivation');
            }
        });
    }
};
