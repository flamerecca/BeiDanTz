<?php

use App\Entities\Vocabulary;
use Illuminate\Database\Seeder;

class VocabularySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Vocabulary::create([
            'vocabulary' => 'apple',
            'answer' => '蘋果',
            'easiest_factor' => 2.5
        ]);
    }
}
