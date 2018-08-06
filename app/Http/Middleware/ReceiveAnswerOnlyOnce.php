<?php

namespace App\Http\Middleware;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Captured;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Illuminate\Support\Facades\Cache;

class ReceiveAnswerOnlyOnce implements Captured
{
    /**
     * Handle a captured message.
     *
     * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $message
     * @param BotMan $bot
     * @param $next
     *
     * @return mixed
     */
    public function captured(IncomingMessage $message, $next, BotMan $bot)
    {
        $payload = $message->getPayload();
        // message_id will unique in all conversation
        $messageId = $payload["message_id"];
        if (!Cache::get($messageId)) {
            Cache::put($messageId, true, 1);
        } else {
            $message->addExtras('skip', true);
        }
        // middleware must return $next($message), otherwise conversation will crash
        return $next($message);
    }
}
