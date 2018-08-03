<?php

namespace App\Criteria;

use App\Entities\TelegramUser;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class NewVocabulariesCriteria.
 *
 * @package namespace App\Criteria;
 */
class NewVocabulariesCriteria implements CriteriaInterface
{
    /**
     * @var TelegramUser
     */
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
            ->whereNotIn('telegram_user_id', [$this->telegramUser->id]);
    }
}
