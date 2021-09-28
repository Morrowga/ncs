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
            $table->id();
            $table->string('uuid')->nullable();
            $table->text('title');
            $table->text('content')->nullable();
            $table->dateTime('published_date');
            $table->string('image_thumbnail')->nullable();
            $table->string('image')->nullable();
            $table->string('source_link')->nullable();
            $table->integer('website_id')->default(0);
            $table->integer('category_id')->default(0);
            $table->boolean('seen')->default(0);
            $table->integer('status')->default(0)->comment("0 for pending,1 for sent,2 for blacklist,3 for duplicate");
            $table->text('remark')->nullable();
            $table->boolean('edited')->default(0)->comment("0 for new,1 for edited");
            $table->softDeletes();
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
