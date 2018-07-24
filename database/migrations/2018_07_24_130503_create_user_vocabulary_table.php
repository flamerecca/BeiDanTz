<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserVocabularyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_vocabulary', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('用戶 ID');
            $table->integer('vocabulary_id')->comment('單字 ID');
            $table->date('review_date')->comment('複習日期');
            $table->decimal('easiest_factor', 12, 3)->comment('單字對用戶簡易程度');
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
        Schema::dropIfExists('user_vocabulary');
    }
}
