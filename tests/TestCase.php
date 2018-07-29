<?php

namespace Tests;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\Studio\Testing\BotManTester;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var BotMan
     */
    protected $botman;

    /**
     * @var BotManTester
     */
    protected $bot;

    /**
     * @var FakeDriver
     */
    protected $fakeDriver;

    protected function assertDatabaseCount(string $table, array $data, int $expectCount)
    {
        $actual = \DB::table($table)->where($data)->count();
        $this->assertEquals($expectCount, $actual);
    }

    protected function receivesInteractiveMessageWithPayload(string $message, array $payload): BotManTester
    {
        $this->fakeDriver->isInteractiveMessageReply = true;
        return $this->bot->receives($message, $payload);
    }
}
