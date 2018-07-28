<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCorrectTimesToTelegramUserVocabularyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_user_vocabulary', function (Blueprint $table) {
            $table->unsignedInteger('correct_times')->default(0)->comment('單字正確次數');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_user_vocabulary', function (Blueprint $table) {
            $table->dropColumn('correct_times');
        });
    }
}
