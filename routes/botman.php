<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});

$botman->hears('單字', function ($bot) {
    $bot->reply('開始背單字!');
});

$botman->hears('Start conversation', BotManController::class.'@startConversation');
