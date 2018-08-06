<?php

namespace App\Criteria;

use App\Entities\TelegramUser;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class PastVocabulariesCriteria.
 *
 * @package namespace App\Criteria;
 */
class PastVocabulariesCriteria implements CriteriaInterface
{
    private $telegramUser;

    /**
     * PastVocabulariesCriteria constructor.
     * @param TelegramUser $telegramUser
     */
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
        return $model->with(['telegramUsers'])
            ->whereHas('telegramUsers', function ($query) {
                $query->where('telegram_user_id', $this->telegramUser->id)
                    ->where('review_date', '<', date('Y-m-d'));
            });
    }
}
