<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('article_id');
            $table->integer('view_count');
            $table->integer('read_count');
            $table->integer('like_count');
            $table->integer('share_count');
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
        Schema::dropIfExists('article_records');
    }
}
