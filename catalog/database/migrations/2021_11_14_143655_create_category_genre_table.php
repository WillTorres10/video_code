<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryGenreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_genre', function (Blueprint $table) {
            $table->foreignUuid('category_id')->references('id')->on('categories');
            $table->foreignUuid('genre_id')->references('id')->on('genres');
            $table->index('category_id');
            $table->index('genre_id');
            $table->unique(['genre_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_genre');
    }
}
