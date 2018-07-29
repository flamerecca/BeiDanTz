<?php

namespace Tests\Unit;

use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;
use App\Services\TestService;
use App\Services\TestServiceInterface;
use App\Services\UserService;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionConversationTest extends TestCase
{
    /**
     * @var QuestionDTO
     */
    private $questionDTO1;

    /**
     * @var QuestionDTO
     */
    private $questionDTO2;

    /**
     * @var Question
     */
    private $questionTemplate1;

    /**
     * @var Question
     */
    private $questionTemplate2;

    /**
     * @ver Mockery\MockInterface
     */
    private $testServiceMock;

    /**
     * @var int
     */
    private $userId = 1;

    public function setUp()
    {
        parent::setUp();
        $this->testServiceMock = Mockery::mock(TestServiceInterface::class);
        $userServiceStub = $this->createMock(UserService::class);
        $user = new TelegramUser(['telegram_id' => 1]);
        $user->id = 1;
        $userServiceStub->method('fistOrCreateUser')->willReturn($user);
        $this->app->instance(TestService::class, $this->testServiceMock);
        $this->app->instance(UserService::class, $userServiceStub);

        $this->questionDTO1 = new QuestionDTO(
            1,
            'test',
            ['測試', '任務', '答案', '回應'],
            0
        );
        $this->questionDTO2 = new QuestionDTO(
            2,
            'question',
            ['任務', '問題', '答案', '回應'],
            1
        );

        $this->questionTemplate1 = Question::create('test')
            ->addButtons([
                Button::create('測試')->value(0),
                Button::create('任務')->value(1),
                Button::create('答案')->value(2),
                Button::create('回應')->value(3),
                Button::create('pass')->value('pass'),
            ]);

        $this->questionTemplate2 = Question::create('question')
            ->addButtons([
                Button::create('任務')->value(0),
                Button::create('問題')->value(1),
                Button::create('答案')->value(2),
                Button::create('回應')->value(3),
                Button::create('pass')->value('pass'),
            ]);

        $this->bot->setUser(['id' => $this->userId]);
    }

    private function mockGetQuestionReturn(array $questions)
    {
        foreach ($questions as $question) {
            $this->testServiceMock
                ->shouldReceive('getQuestion')
                ->once()
                ->andReturn($question);
        }
    }

    private function shouldReceiveAnswer(AnswerDTO $answer)
    {
        $this->testServiceMock
            ->shouldReceive('answer')
            ->once()
            ->with(Mockery::on(function (AnswerDTO $ans) use ($answer) {
                return $ans == $answer;
            }));
    }

    /**
     * @test
     */
    public function 測試能夠取得正確的問題樣板()
    {
        $this->mockGetQuestionReturn([$this->questionDTO1]);

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true);
    }

    /**
     * @test
     */
    public function 測試在最短時間內回答正確時會進入下一題，並且TestService會收到最短時間內回答的通知()
    {
        $this->mockGetQuestionReturn([$this->questionDTO1, $this->questionDTO2]);
        $this->shouldReceiveAnswer(new AnswerDTO(
            $this->userId,
            $this->questionDTO1->getVocabularyId(),
            AnswerDTO::CORRECT_LESS_MIN_TIME
        ));

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer())
            ->assertReply('答對惹')
            ->assertTemplate($this->questionTemplate2, true);
    }

    /**
     * @test
     */
    public function 測試在介於最短時間跟最長時限內回答正確時，TestService會收到介於時限內回答的通知()
    {
        $this->mockGetQuestionReturn([$this->questionDTO1, $this->questionDTO2]);
        $this->shouldReceiveAnswer(new AnswerDTO(
            $this->userId,
            $this->questionDTO1->getVocabularyId(),
            AnswerDTO::CORRECT_BETWEEN_MIN_MAX_TIME
        ));

        $this->bot->receives('開始複習');
        $wait = (config('botman.config.answer_min_time') + config('botman.config.answer_max_time')) / 2;
        usleep($wait * 1000);
        $this->bot->receivesInteractiveMessage($this->questionDTO1->getAnswer());
    }

    /**
     * @test
     */
    public function 測試在超出最長時限後回答正確時，TestService會收到超出時限後回答正確的通知()
    {
        $this->mockGetQuestionReturn([$this->questionDTO1, $this->questionDTO2]);
        $this->shouldReceiveAnswer(new AnswerDTO(
            $this->userId,
            $this->questionDTO1->getVocabularyId(),
            AnswerDTO::CORRECT_OVER_MAX_TIME
        ));

        $this->bot->receives('開始複習');
        $wait = (config('botman.config.answer_min_time') + config('botman.config.answer_max_time'));
        usleep($wait * 1000);
        $this->bot->receivesInteractiveMessage($this->questionDTO1->getAnswer());
    }

    /**
     * @test
     */
    public function 測試回答錯誤時可以重複回答問題，第二次回答正確後TestService可收到錯誤一次的通知()
    {
        $this->mockGetQuestionReturn([$this->questionDTO1, $this->questionDTO2]);
        $this->shouldReceiveAnswer(new AnswerDTO(
            $this->userId,
            $this->questionDTO1->getVocabularyId(),
            AnswerDTO::WRONG_ONCE
        ));

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer() + 1)
            ->assertReply('答錯惹')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer())
            ->assertReply('答對惹');
    }

    /**
     * @test
     */
    public function 測試再次回答錯誤後詢問新問題，TestService收到錯誤兩次的通知()
    {
        $this->mockGetQuestionReturn([$this->questionDTO1, $this->questionDTO2]);
        $this->shouldReceiveAnswer(new AnswerDTO(
            $this->userId,
            $this->questionDTO1->getVocabularyId(),
            AnswerDTO::WRONG_TWICE
        ));

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer() + 1)
            ->assertReply('答錯惹')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer() + 1)
            ->assertReply('答錯惹')
            ->assertTemplate($this->questionTemplate2, true);
    }

    /**
     * @test
     */
    public function 測試收到pass則直接詢問新問題，並且service會收到pass的通知()
    {
        $this->mockGetQuestionReturn([$this->questionDTO1, $this->questionDTO2]);
        $this->shouldReceiveAnswer(new AnswerDTO(
            $this->userId,
            $this->questionDTO1->getVocabularyId(),
            AnswerDTO::PASS
        ));

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage('pass')
            ->assertReply('跳過')
            ->assertTemplate($this->questionTemplate2, true);
    }
}
