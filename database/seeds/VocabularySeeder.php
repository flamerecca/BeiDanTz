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
            'content' => 'apple',
            'answer' => '蘋果',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'apple',
            'answer' => '蘋果',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'test',
            'answer' => '測試',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'mission',
            'answer' => '任務',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'answer',
            'answer' => '答案',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'response',
            'answer' => '回應',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'question',
            'answer' => '問題',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'eloquent',
            'answer' => '有說服力的',
            'easiest_factor' => 2.5
        ]);
    }
}
