<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['telegram_id'];

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
