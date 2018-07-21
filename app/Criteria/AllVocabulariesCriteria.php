<?php

namespace App\Criteria;

use App\Entities\TelegramUser;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class AllVocabulariesCriteria.
 *
 * @package namespace App\Criteria;
 */
class AllVocabulariesCriteria implements CriteriaInterface
{
    private $telegramUser;

    public function __construct(TelegramUser $telegramUser)
    {
        $this->telegramUser = $telegramUser;
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
        $model = $model->with(['telegramUsers']);
        return $model;
    }
}
