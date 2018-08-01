<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/24
 * Time: 23:10
 */

namespace Tests\Unit;

use App\Criteria\LimitCriteria;
use App\Criteria\TodayVocabulariesCriteria;
use App\Criteria\DifferentVocabularyCriteria;
use App\DTO\AnswerDTO;
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
            $this->isInstanceOf(TodayVocabulariesCriteria::class)
        )->will($this->onConsecutiveCalls(
            collect([$vocabulary])
        ));

        $mock->method('pushCriteria')->withConsecutive(
            $this->isInstanceOf(DifferentVocabularyCriteria::class),
            $this->isInstanceOf(LimitCriteria::class)
        )->will($this->onConsecutiveCalls(
            $mock,
            $wrongAnswers
        ));

        app()->instance(VocabularyRepositoryEloquent::class, $mock);

        $service = app()->make(TestService::class);
        $q = $service->getQuestion($this->telegramUser);
        $this->assertEquals($vocabulary->answer, $q->getAnswer());
    }

    public function testIfUserHasNoVocabularyGetQuestionShouldWorkProperly()
    {
        $testService = app()->make(TestService::class);

        $question = $testService->getQuestion($this->telegramUser);
        $this->assertInstanceOf(QuestionDTO::class, $question);
        $this->assertEquals(4, count($question->getOptions()));
    }

    public function testAnswerFirstTimePass()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 1.700,
            'correct_times' => 0,
        ]);
    }

    public function testAnswerFirstTimeWrongTwice()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            3,
            AnswerDTO::WRONG_TWICE
        );
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 1.960,
            'correct_times' => 0,
        ]);
    }

    public function testAnswerFirstTimeWrongOnce()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            3,
            AnswerDTO::WRONG_ONCE
        );
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 2.180,
            'correct_times' => 0,
        ]);
    }

    public function testAnswerFirstTimeCorrectOverMaxTime()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            3,
            AnswerDTO::CORRECT_OVER_MAX_TIME
        );
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 3 day'))->format('Y-m-d'),
            'easiest_factor' => 2.360,
            'correct_times' => 1,
        ]);
    }

    public function testAnswerFirstTimeCorrectBetweenMinMaxTime()
    {
        $vocabularyId = 4;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_BETWEEN_MIN_MAX_TIME
        );
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 3 day'))->format('Y-m-d'),
            'easiest_factor' => 2.500,
            'correct_times' => 1,
        ]);
    }

    public function testAnswerFirstTimeCorrectLessMinTime()
    {
        $vocabularyId = 4;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_LESS_MIN_TIME
        );
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 3 day'))->format('Y-m-d'),
            'easiest_factor' => 2.600,
            'correct_times' => 1,
        ]);
    }

    public function testAnswerNotFirstTimePass()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $this->telegramUser->vocabularies()->attach([
            $vocabularyId => [
                'review_date' => '2018-01-01',
                'easiest_factor' => 5.000,
                'correct_times' => 10,
            ]
        ]);
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 4.200,
            'correct_times' => 0,
        ]);
    }

    public function testAnswerNotFirstTimeWrongTwice()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::WRONG_TWICE
        );
        $this->telegramUser->vocabularies()->attach([
            $vocabularyId => [
                'review_date' => '2018-01-01',
                'easiest_factor' => 5.000,
                'correct_times' => 10,
            ]
        ]);
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 4.460,
            'correct_times' => 0,
        ]);
    }

    public function testAnswerNotFirstTimeWrongOnce()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::WRONG_ONCE
        );
        $this->telegramUser->vocabularies()->attach([
            $vocabularyId => [
                'review_date' => '2018-01-01',
                'easiest_factor' => 5.000,
                'correct_times' => 10,
            ]
        ]);
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 4.680,
            'correct_times' => 0,
        ]);
    }

    public function testAnswerNotFirstTimeCorrectOverMaxTime()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_OVER_MAX_TIME
        );
        $this->telegramUser->vocabularies()->attach([
            $vocabularyId => [
                'review_date' => '2018-01-01',
                'easiest_factor' => 2.900,
                'correct_times' => 5,
            ]
        ]);
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 615 day'))->format('Y-m-d'),
            'easiest_factor' => 2.760,
            'correct_times' => 6,
        ]);
    }

    public function testAnswerWrongShouldNotOverwriteOtherVocabulary()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $this->telegramUser->vocabularies()->attach([
            1 => [
                'review_date' => '2018-01-01',
                'easiest_factor' => 5.000,
                'correct_times' => 10,
            ]
        ]);
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => 1,
            'review_date' => '2018-01-01',
            'easiest_factor' => 5.000,
            'correct_times' => 10,
        ]);
    }

    public function testAnswerRightShouldNotOverwriteOtherVocabulary()
    {
        $vocabularyId = 3;
        $testService = app()->make(TestService::class);
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $this->telegramUser->vocabularies()->attach([
            1 => [
                'review_date' => '2018-01-01',
                'easiest_factor' => 5.000,
                'correct_times' => 10,
            ]
        ]);
        $testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => 1,
            'review_date' => '2018-01-01',
            'easiest_factor' => 5.000,
            'correct_times' => 10,
        ]);
    }
}