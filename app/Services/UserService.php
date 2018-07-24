<?php

namespace App\Services;

use App\Entities\TelegramUser;
use App\Repositories\TelegramUserRepository;
use App\Repositories\TelegramUserRepositoryEloquent;

class UserService
{
    /**
     * @var TelegramUserRepositoryEloquent
     */
    private $repository;

    public function __construct(TelegramUserRepositoryEloquent $repository)
    {
        $this->repository = $repository;
    }

    public function fistOrCreateUser(int $userId): TelegramUser
    {
        return $this->repository->firstOrCreate(['telegram_id' => $userId]);
    }
}