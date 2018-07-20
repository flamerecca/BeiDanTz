<?php

namespace App\Presenters;

use App\Transformers\VocabularyTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class VocabularyPresenter.
 *
 * @package namespace App\Presenters;
 */
class VocabularyPresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new VocabularyTransformer();
    }
}
