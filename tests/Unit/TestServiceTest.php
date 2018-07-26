<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/24
 * Time: 23:10
 */

namespace Tests\Unit;

use App\DTO\QuestionDTO;
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;
use App\Repositories\TelegramUserRepository;
use App\Repositories\TelegramUserRepositoryEloquent;
use App\Repositories\VocabularyRepository;
use App\Repositories\VocabularyRepositoryEloquent;
use App\Services\TestService;
use Mockery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TestServiceTest extends TestCase
{
    use DatabaseTransactions;

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
    }

    public function testGetQuestionShouldReturnQuestionDTO()
    {

        $vocabulary = Vocabulary::find(3);

        $vocabulary->telegramUsers()->attach(
            1,
            [
                'review_date' => '2018-07-25',
                'easiest_factor' => 0
            ]
        );

        $testService = new TestService(
            new VocabularyRepositoryEloquent(app()),
            new TelegramUserRepositoryEloquent(app())
        );
        $telegramUser = new TelegramUser();
        $telegramUser->id = 1;
        $question = $testService->getQuestion($telegramUser);
        $this->assertInstanceOf(QuestionDTO::class, $question);
        $this->assertEquals(4, count($question->getOptions()));
    }

    public function testGetQuestionAnswerIsCorrect()
    {
        $vocabulary = Vocabulary::find(3);

        $vocabulary->telegramUsers()->attach(
            1,
            [
                'review_date' => '2018-07-25',
                'easiest_factor' => 0
            ]
        );

        $testService = new TestService(
            new VocabularyRepositoryEloquent(app()),
            new TelegramUserRepositoryEloquent(app())
        );
        $telegramUser = new TelegramUser();
        $telegramUser->id = 1;
        $question = $testService->getQuestion($telegramUser);
        $this->assertEquals($question->getVocabulary()->answer, $question->getOptions()[$question->getAnswer()]);
    }

    public function testIfUserHasNoVocabularyGetQuestionShouldWorkProperly()
    {

        TelegramUser::create([
            'telegram_id' => '1'
        ]);


        $testService = new TestService(
            app()->make(VocabularyRepository::class),
            app()->make(TelegramUserRepository::class)
        );
        $telegramUser = new TelegramUser();
        $telegramUser->id = 1;

        $question = $testService->getQuestion($telegramUser);
        $this->assertInstanceOf(QuestionDTO::class, $question);
        $this->assertEquals(4, count($question->getOptions()));
    }
}