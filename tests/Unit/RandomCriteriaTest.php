<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/26
 * Time: 00:33
 */

namespace Tests\Unit;

use App\Entities\Vocabulary;
use App\Repositories\VocabularyRepositoryEloquent;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Criteria\RandomCriteria;

class RandomCriteriaTest extends TestCase
{
    use DatabaseTransactions;

    public function testRandomCriteriaShouldReturnCollection()
    {
        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        $vocabulary = (new VocabularyRepositoryEloquent(app()))->getByCriteria(new RandomCriteria());
        $this->assertInstanceOf(Collection::class, $vocabulary);
    }

}