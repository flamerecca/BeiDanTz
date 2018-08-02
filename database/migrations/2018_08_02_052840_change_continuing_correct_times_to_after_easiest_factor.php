<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeContinuingCorrectTimesToAfterEasiestFactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE telegram_user_vocabulary MODIFY COLUMN continuing_correct_times INTEGER (11) UNSIGNED AFTER easiest_factor");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE telegram_user_vocabulary MODIFY COLUMN continuing_correct_times INTEGER (11) AFTER updated_at");
    }
}
