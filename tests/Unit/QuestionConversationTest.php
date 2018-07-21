<?php

namespace Tests\Unit;

use App\DTO\QuestionDTO;
use App\Entities\Vocabulary;
use App\Services\TestService;
use App\Services\TestServiceInterface;
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
     * @ver Mock
     */
    private $testServiceMock;

    public function setUp()
    {
        parent::setUp();
        $this->testServiceMock = Mockery::mock(TestServiceInterface::class);
        $this->app->instance(TestService::class, $this->testServiceMock);

        $v1 = new Vocabulary([
            'id' => 1,
            'content' => 'test',
            'answer' => '測試',
            'easiest_factor' => 2.5,
        ]);
        $v2 = new Vocabulary([
            'id' => 2,
            'content' => 'question',
            'answer' => '問題',
            'easiest_factor' => 2.5,
        ]);
        $this->questionDTO1 = new QuestionDTO($v1, ['測試', '任務', '答案', '回應'], 0);
        $this->questionDTO2 = new QuestionDTO($v2, ['任務', '問題', '答案', '回應'], 1);

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
    }

    /**
     * @test
     */
    public function 測試能夠取得正確的問題樣板()
    {
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO1);

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true);
    }

    /**
     * @test
     */
    public function 測試回答正確時會進入下一題()
    {
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO1);
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO2);

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer())
            ->assertTemplate($this->questionTemplate2, true);
    }

    /**
     * @test
     */
    public function 測試回答錯誤時可以重複回答問題()
    {
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO1);

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer() + 1)
            ->assertTemplate($this->questionTemplate1, true);
    }

    /**
     * @test
     */
    public function 測試再次回答錯誤後詢問新問題()
    {
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO1);
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO2);

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer() + 1)
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage($this->questionDTO1->getAnswer() + 1)
            ->assertTemplate($this->questionTemplate2, true);
    }

    /**
     * @test
     */
    public function 測試收到pass則直接詢問新問題()
    {
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO1);
        $this->testServiceMock
            ->shouldReceive('getQuestion')
            ->once()
            ->andReturn($this->questionDTO2);

        $this->bot
            ->receives('開始複習')
            ->assertTemplate($this->questionTemplate1, true)
            ->receivesInteractiveMessage('pass')
            ->assertTemplate($this->questionTemplate2, true);
    }
}
