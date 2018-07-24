<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class TelegramUser.
 *
 * @package namespace App\Entities;
 */
class TelegramUser extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reviewingVocabularies()
    {
        return $this->belongsToMany(Vocabulary::class)
            ->withPivot('review_date', 'easiest_factor')
            ->wherePivot('review_date', date('Y-m-d'));
    }
}
