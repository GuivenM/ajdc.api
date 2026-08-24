<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('guide_sous_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('section_id')->constrained('guide_sections')->cascadeOnDelete();
            $table->string('titre');
            $table->text('contenu')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->enum('statut', ['publie', 'brouillon'])->default('brouillon');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guide_sous_sections');
    }
};
