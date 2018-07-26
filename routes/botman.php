<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('/start', BotManController::class.'@welcomeMessage');

$botman->hears('開始複習', BotManController::class.'@startBeiDanTz');

$botman->hears('help', BotManController::class.'@help');
