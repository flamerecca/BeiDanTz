<?php

namespace App\Criteria;

use App\Entities\Vocabulary;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class WrongAnswerCriteria.
 *
 * @package namespace App\Criteria;
 */
class WrongAnswerCriteria implements CriteriaInterface
{
    private $vocabulary;
    public function __construct(Vocabulary $vocabulary)
    {
        $this->vocabulary = $vocabulary;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->where('id', '!=', $this->vocabulary->id)
            ->inRandomOrder()
            ->limit(4);
        return $model;
    }
}
