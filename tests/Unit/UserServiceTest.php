<?php

namespace Tests\Feature;

use App\Entities\TelegramUser;
use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var UserService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = app()->make(UserService::class);
    }

    /**
     * @group UserServiceTest
     * @test
     */
    public function testFirstOrCreateWillCreateUserWhenCantFoundUser()
    {
        $userId = rand(0, 10);
        $user = $this->service->fistOrCreateUser($userId);

        $this->assertDatabaseHas('telegram_users', [
            'telegram_id' => $userId,
        ]);
        $this->assertEquals($user->telegram_id, $userId);
    }

    /**
     * @group UserServiceTest
     * @test
     */
    public function testFirstOrCreateCanFoundUserIfUserExists()
    {
        $userId = rand(0, 10);
        TelegramUser::create([
            'telegram_id' => $userId,
        ]);
        $this->assertDatabaseHas('telegram_users', [
            'telegram_id' => $userId,
        ]);
        $user = $this->service->fistOrCreateUser($userId);

        $this->assertDatabaseCount('telegram_users', [
            'telegram_id' => $userId,
        ], 1);
        $this->assertEquals($user->telegram_id, $userId);
    }
}
