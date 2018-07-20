<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\TelegramUserRepository;
use App\Entities\TelegramUser;
use App\Validators\TelegramUserValidator;

/**
 * Class TelegramUserRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class TelegramUserRepositoryEloquent extends BaseRepository implements TelegramUserRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return TelegramUser::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return TelegramUserValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
