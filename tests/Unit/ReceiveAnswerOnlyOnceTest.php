<?php

namespace Tests\Feature;

use App\Http\Middleware\ReceiveAnswerOnlyOnce;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReceiveAnswerOnlyOnceTest extends TestCase
{
    public function testFirstRequestShouldPass()
    {
        $message = new IncomingMessage('', '', '');
        $middleware = new ReceiveAnswerOnlyOnce();
        $closure = function (IncomingMessage $message) {
            $this->assertEquals(false, $message->getExtras('skip'));
        };

        $middleware->captured($message, $closure, $this->botman);
    }

    public function testSecondRequestShouldSkip()
    {
        $messageId = rand(1000, 9999);
        $message = new IncomingMessage('', '', '', ['message_id' => $messageId]);
        $middleware = new ReceiveAnswerOnlyOnce();
        Cache::put($messageId, true, 1);
        $closure = function (IncomingMessage $message) {
            $this->assertEquals(true, $message->getExtras('skip'));
        };

        $middleware->captured($message, $closure, $this->botman);
    }
}
