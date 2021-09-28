<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexingArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indexing_articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host');
            $table->string('url');
            $table->string('imageUrl');
            $table->string('title', 355);
            $table->string('providerCategory');
            $table->longText('content');
            $table->string('publishedDate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('indexing_articles');
    }
}
