<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('membres', function (Blueprint $table) {
            if (!Schema::hasColumn('membres', 'ville')) {
                $table->string('ville')->nullable()->after('whatsapp');
            }
        });
    }

    public function down()
    {
        Schema::table('membres', function (Blueprint $table) {
            if (Schema::hasColumn('membres', 'ville')) {
                $table->dropColumn('ville');
            }
        });
    }
};
