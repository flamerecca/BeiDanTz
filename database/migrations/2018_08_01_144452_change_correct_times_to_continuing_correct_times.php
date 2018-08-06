<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCorrectTimesToContinuingCorrectTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_user_vocabulary', function(Blueprint $table) {
            $table->renameColumn('correct_times', 'continuing_correct_times');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_user_vocabulary', function(Blueprint $table) {
            $table->renameColumn('continuing_correct_times', 'correct_times');
        });
    }
}
