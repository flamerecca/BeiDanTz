<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/24
 * Time: 23:10
 */

namespace Tests\Unit;

use App\Criteria\LimitCriteria;
use App\Criteria\RandomCriteria;
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
    private $testService;

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

        $this->testService = app()->make(TestService::class);

    }

    public function testGetQuestionShouldReturnQuestionDTO()
    {
        $question = $this->testService->getQuestion($this->telegramUser);
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
            $this->isInstanceOf(RandomCriteria::class),
            $this->isInstanceOf(LimitCriteria::class)
        )->will($this->onConsecutiveCalls(
            $mock,
            $mock,
            $wrongAnswers
        ));

        app()->instance(VocabularyRepositoryEloquent::class, $mock);
        $this->testService = app()->make(TestService::class);
        $q = $this->testService->getQuestion($this->telegramUser);
        $this->assertEquals($vocabulary->answer, $q->getAnswer());
    }

    public function testIfUserHasNoVocabularyGetQuestionShouldWorkProperly()
    {
        $question = $this->testService->getQuestion($this->telegramUser);
        $this->assertInstanceOf(QuestionDTO::class, $question);
        $this->assertEquals(4, count($question->getOptions()));
    }

    public function testIfUserHasTodayVocabularyGetQuestionShouldReturnTodayVocabulary()
    {
        $vocabularyId = 2;
        $this->telegramUser->vocabularies()->sync([
            $vocabularyId => [
                'review_date' =>  (new \DateTime('today'))->format('Y-m-d'),
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);
        $question = $this->testService->getQuestion($this->telegramUser);
        $this->assertEquals($question->getVocabularyId(), $vocabularyId);
    }

    public function testIfUserHasPreviousVocabularyGetQuestionShouldReturnPreviousVocabulary()
    {
        $vocabularyId = 2;
        $todayVocabularyId = 4;
        $this->telegramUser->vocabularies()->attach([
            $vocabularyId => [
                'review_date' =>  '1987-01-01',
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);

        $this->telegramUser->vocabularies()->attach([
            $todayVocabularyId => [
                'review_date' =>  (new \DateTime('today'))->format('Y-m-d'),
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);

        $question = $this->testService->getQuestion($this->telegramUser);
        $this->assertEquals($question->getVocabularyId(), $vocabularyId);
    }

    public function testAnswerFirstTimePass()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 1.700,
            'continuing_correct_times' => 0,
        ]);
    }

    public function testAnswerFirstTimeWrongTwice()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::WRONG_TWICE
        );
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 1.960,
            'continuing_correct_times' => 0,
        ]);
    }

    public function testAnswerFirstTimeWrongOnce()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            3,
            AnswerDTO::WRONG_ONCE
        );
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 2.180,
            'continuing_correct_times' => 0,
        ]);
    }

    public function testAnswerFirstTimeCorrectOverMaxTime()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            3,
            AnswerDTO::CORRECT_OVER_MAX_TIME
        );
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 3 day'))->format('Y-m-d'),
            'easiest_factor' => 2.360,
            'continuing_correct_times' => 1,
        ]);
    }

    public function testAnswerFirstTimeCorrectBetweenMinMaxTime()
    {
        $vocabularyId = 4;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_BETWEEN_MIN_MAX_TIME
        );
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 3 day'))->format('Y-m-d'),
            'easiest_factor' => 2.500,
            'continuing_correct_times' => 1,
        ]);
    }

    public function testAnswerFirstTimeCorrectLessMinTime()
    {
        $vocabularyId = 4;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_LESS_MIN_TIME
        );
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 3 day'))->format('Y-m-d'),
            'easiest_factor' => 2.600,
            'continuing_correct_times' => 1,
        ]);
    }

    public function testAnswerNotFirstTimePass()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $this->attachVocabulary($vocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 2.200,
            'continuing_correct_times' => 0,
        ]);
    }

    private function attachVocabulary(int $vocabularyId): void
    {
        $this->telegramUser->vocabularies()->sync([
            $vocabularyId => [
                'review_date' => '2018-01-01',
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);
    }

    public function testAnswerNotFirstTimeWrongTwice()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::WRONG_TWICE
        );
        $this->attachVocabulary($vocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 2.460,
            'continuing_correct_times' => 0,
        ]);
    }

    public function testAnswerNotFirstTimeWrongOnce()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::WRONG_ONCE
        );
        $this->attachVocabulary($vocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'easiest_factor' => 2.680,
            'continuing_correct_times' => 0,
        ]);
    }

    public function testAnswerNotFirstTimeCorrectOverMaxTime()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_OVER_MAX_TIME
        );
        $this->attachVocabulary($vocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 729 day'))->format('Y-m-d'),
            'easiest_factor' => 2.860,
            'continuing_correct_times' => 6,
        ]);
    }

    public function testAnswerNotFirstTimeCorrectBetweenMinMaxTime()
    {
        $vocabularyId = 3;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_BETWEEN_MIN_MAX_TIME
        );
        $this->attachVocabulary($vocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 729 day'))->format('Y-m-d'),
            'easiest_factor' => 3.000,
            'continuing_correct_times' => 6,
        ]);
    }

    public function testAnswerNotFirstTimeCorrectLessMinTime()
    {
        $vocabularyId = 2;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::CORRECT_LESS_MIN_TIME
        );
        $this->attachVocabulary($vocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $vocabularyId,
            'review_date' => (new \DateTime('today + 729 day'))->format('Y-m-d'),
            'easiest_factor' => 3.100,
            'continuing_correct_times' => 6,
        ]);
    }

    public function testAnswerWrongShouldNotOverwriteOtherVocabulary()
    {
        $vocabularyId = 3;
        $originVocabularyId = 1;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $this->attachVocabulary($originVocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $originVocabularyId,
            'review_date' => '2018-01-01',
            'easiest_factor' => 3.000,
            'continuing_correct_times' => 5,
        ]);
    }

    public function testAnswerRightShouldNotOverwriteOtherVocabulary()
    {
        $vocabularyId = 3;
        $originVocabularyId = 1;
        $answerDTO = new AnswerDTO(
            $this->telegramUser->telegram_id,
            $vocabularyId,
            AnswerDTO::PASS
        );
        $this->attachVocabulary($originVocabularyId);
        $this->testService->answer($answerDTO);
        $this->assertDatabaseHas('telegram_user_vocabulary', [
            'telegram_user_id' => $this->telegramUser->telegram_id,
            'vocabulary_id' => $originVocabularyId,
            'review_date' => '2018-01-01',
            'easiest_factor' => 3.000,
            'continuing_correct_times' => 5,
        ]);
    }
}