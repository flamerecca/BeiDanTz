<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entities\Vocabulary;

/**
 * Class VocabularyTransformer.
 *
 * @package namespace App\Transformers;
 */
class VocabularyTransformer extends TransformerAbstract
{
    /**
     * Transform the Vocabulary entity.
     *
     * @param \App\Entities\Vocabulary $model
     *
     * @return array
     */
    public function transform(Vocabulary $model)
    {
        return [
            'id'         => (int) $model->id,

            /* place your other model properties here */

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
