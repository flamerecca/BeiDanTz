<?php

namespace App\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class LimitCriteria.
 *
 * @package namespace App\Criteria;
 */
class LimitCriteria implements CriteriaInterface
{
    private $number;
    public function __construct(int $number)
    {
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
        return $model->limit($this->number);
    }
}
