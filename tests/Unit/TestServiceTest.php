<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/24
 * Time: 23:10
 */

namespace Tests\Unit;

use App\Criteria\TodayVocabulariesCriteria;
use App\Criteria\WrongAnswerCriteria;
use App\DTO\QuestionDTO;
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;
use App\Repositories\VocabularyRepositoryEloquent;
use App\Services\TestService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TestServiceTest extends TestCase
{
    use DatabaseTransactions;
    private $telegramUser;

    public function setUp()
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

        $this->telegramUser = TelegramUser::create([
            'telegram_id' => '1'
        ]);

    }

    public function testGetQuestionShouldReturnQuestionDTO()
    {
        $testService = app()->make(TestService::class);
        $question = $testService->getQuestion($this->telegramUser);
        $this->assertInstanceOf(QuestionDTO::class, $question);
        $this->assertEquals(4, count($question->getOptions()));
    }

    public function testGetQuestionAnswerIsCorrect()
    {
        $vocabulary = Vocabulary::find(3);
        $wrongAnswers = Vocabulary::where('id', '!=', 3)->limit(4)->get();

        $mock = $this->createMock(VocabularyRepositoryEloquent::class);
        $mock->method('getByCriteria')->withConsecutive(
            $this->isInstanceOf(TodayVocabulariesCriteria::class),
            $this->isInstanceOf(WrongAnswerCriteria::class)
        )->will($this->onConsecutiveCalls(
            collect([$vocabulary]),
            collect($wrongAnswers)
        ));

        app()->instance(VocabularyRepositoryEloquent::class, $mock);

        $service = app()->make(TestService::class);
        $q = $service->getQuestion($this->telegramUser);
        $this->assertEquals($vocabulary->answer, $q->getOptions()[$q->getAnswer()]);
    }

    public function testIfUserHasNoVocabularyGetQuestionShouldWorkProperly()
    {


        $testService = app()->make(TestService::class);

        $question = $testService->getQuestion($this->telegramUser);
        $this->assertInstanceOf(QuestionDTO::class, $question);
        $this->assertEquals(4, count($question->getOptions()));
    }
}