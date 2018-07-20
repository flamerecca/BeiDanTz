<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\VocabularyRepository;
use App\Entities\Vocabulary;
use App\Validators\VocabularyValidator;

/**
 * Class VocabularyRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class VocabularyRepositoryEloquent extends BaseRepository implements VocabularyRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Vocabulary::class;
    }

    /**
    * Specify Validator class name
    *
    * @return mixed
    */
    public function validator()
    {

        return VocabularyValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
