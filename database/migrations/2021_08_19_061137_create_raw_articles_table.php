<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_articles', function (Blueprint $table) {
            $table->increments('id');
            // $table->uuid('article_ids')->primary();
            $table->string('title', 355);
            $table->longText('content')->nullable();
            $table->string('host')->nullable();
            $table->dateTime('publishedDate')->nullable();
            $table->longText('thumbnailUrl')->nullable();
            $table->string('fontType')->nullable();
            $table->tinyInteger('sent_status')->default(0);
            $table->integer('update_status')->default(0);
            $table->longText('image')->nullable();
            $table->string('uuid')->nullable();
            $table->longText('source_link', 355)->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('website_id')->nullable();
            // $table->foreign('category_id')
            //     ->references('id')
            //     ->on('categories')
            //     ->onUpdate('cascade')
            //     ->onDelete('set null');
            // $table->foreign('website_id')
            //     ->references('id')
            //     ->on('websites')
            //     ->onUpdate('cascade')
            //     ->onDelete('set null');
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
        Schema::dropIfExists('raw_articles');
    }
}
