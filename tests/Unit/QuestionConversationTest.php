<?php

namespace Tests\Unit;

use App\DTO\QuestionDTO;
use App\Entities\Vocabulary;
use App\Services\TestService;
use App\Services\TestServiceInterface;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionConversationTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $mock = $this->createMock(TestServiceInterface::class);
        $v = new Vocabulary([
            'id' => 1,
            'content' => 'test',
            'answer' => '測試',
            'easiest_factor' => 2.5,
        ]);
        $q = new QuestionDTO($v, ['測試', '任務', '答案', '回應'], 0);
        $mock->method('getQuestion')->willReturn($q);
        $this->app->instance(TestService::class, $mock);
    }

    /**
     * @test
     */
    public function 測試能夠取得正確的問題樣板()
    {
        $q = Question::create('test')
            ->addButtons([
                Button::create('測試')->value(0),
                Button::create('任務')->value(1),
                Button::create('答案')->value(2),
                Button::create('回應')->value(3),
                Button::create('pass')->value('pass'),
            ]);
        $this->bot
            ->receives('開始複習')
            ->assertTemplate($q, true);
    }
}
