<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entities\TelegramUser;

/**
 * Class TelegramUserTransformer.
 *
 * @package namespace App\Transformers;
 */
class TelegramUserTransformer extends TransformerAbstract
{
    /**
     * Transform the TelegramUser entity.
     *
     * @param \App\Entities\TelegramUser $model
     *
     * @return array
     */
    public function transform(TelegramUser $model)
    {
        return [
            'id'         => (int) $model->id,

            /* place your other model properties here */

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
