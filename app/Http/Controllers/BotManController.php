<?php

namespace App\Http\Controllers;

use App\Http\Conversations\QuestionConversation;
use App\Services\TestService;
use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Conversations\ExampleConversation;
use Illuminate\Support\Facades\Storage;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    public function welcomeMessage(BotMan $bot)
    {
        $welcomeMessage = Storage::disk('local')->get('welcomeMessage.txt');

        $bot->reply($welcomeMessage);
    }

    public function startBeiDanTz(BotMan $bot)
    {
        $bot->startConversation(new QuestionConversation());
    }
}
