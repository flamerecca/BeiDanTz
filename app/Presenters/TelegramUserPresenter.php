<?php

namespace App\Presenters;

use App\Transformers\TelegramUserTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class TelegramUserPresenter.
 *
 * @package namespace App\Presenters;
 */
class TelegramUserPresenter extends FractalPresenter
{
    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new TelegramUserTransformer();
    }
}
