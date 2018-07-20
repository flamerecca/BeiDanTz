<?php

namespace App\Providers;

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
        $this->app->bind(\App\Repositories\VocabularyRepository::class, \App\Repositories\VocabularyRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\TelegramUserRepository::class, \App\Repositories\TelegramUserRepositoryEloquent::class);
        //:end-bindings:
    }
}
