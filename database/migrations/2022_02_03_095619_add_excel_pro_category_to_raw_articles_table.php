<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExcelProCategoryToRawArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raw_articles', function (Blueprint $table) {
            //
            $table->string('excel_pro_category')->after('website_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_articles', function (Blueprint $table) {
            //
            $table->dropColumn('excel_pro_category');
        });
    }
}
