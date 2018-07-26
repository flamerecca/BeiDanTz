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
    private $number;
    public function __construct(Vocabulary $vocabulary, int $number)
    {
        $this->vocabulary = $vocabulary;
        $this->number = $number;
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
            ->limit($this->number);
        return $model;
    }
}
