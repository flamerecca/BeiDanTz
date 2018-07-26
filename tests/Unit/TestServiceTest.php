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
use App\Services\TestService;
use Mockery;
use Tests\TestCase;

class TestServiceTest extends TestCase
{
    public function testGetQuestionShouldReturnQuestionDTO()
    {
        $testService = new TestService();
        $telegramUser = new TelegramUser();
        $this->assertInstanceOf(QuestionDTO::class, $testService->getQuestion($telegramUser));
    }
}