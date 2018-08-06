<?php
use App\Http\Controllers\BotManController;
use App\Http\Middleware\ReceiveAnswerOnlyOnce;

$botman = resolve('botman');

$botman->hears('/start', BotManController::class.'@welcomeMessage');

$botman->middleware->captured(new ReceiveAnswerOnlyOnce());
$botman->hears('開始複習', BotManController::class.'@startBeiDanTz');

$botman->hears('help', BotManController::class.'@help');
