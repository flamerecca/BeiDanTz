<?php

namespace App\Providers;

use App\Repositories\TelegramUserRepository;
use App\Repositories\TelegramUserRepositoryEloquent;
use App\Repositories\VocabularyRepository;
use App\Repositories\VocabularyRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            VocabularyRepository::class,
            VocabularyRepositoryEloquent::class
        );
        $this->app->bind(
            TelegramUserRepository::class,
            TelegramUserRepositoryEloquent::class
        );
        //:end-bindings:
    }
}
