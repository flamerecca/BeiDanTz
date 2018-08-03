<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/29
 * Time: 10:10
 */

namespace Tests;

use App\Criteria\DifferentVocabularyCriteria;
use App\Criteria\WrongAnswerCriteria;
use App\Entities\Vocabulary;
use App\Repositories\VocabularyRepository;
use Illuminate\Support\Collection;

class DifferentVocabularyCriteriaTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        
        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'apple',
            'answer' => '蘋果',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'cat',
            'answer' => '貓',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'dog',
            'answer' => '狗',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'egg',
            'answer' => '雞蛋',
            'easiest_factor' => 2.5
        ]);
    }

    public function testWrongAnswerShouldReturnCollection()
    {
        $repository = app()->make(VocabularyRepository::class);
        $vocabulary = Vocabulary::find(1);
        $wrongAnswer = $repository->getByCriteria(
            new DifferentVocabularyCriteria($vocabulary)
        );
        $this->assertInstanceOf(Collection::class, $wrongAnswer);
    }
}