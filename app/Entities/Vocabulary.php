<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Vocabulary.
 *
 * @package namespace App\Entities;
 */
class Vocabulary extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
        'answer',
        'easiest_factor',
    ];

    protected $table = 'vocabularies';

    /**
     * @return BelongsToMany
     */
    public function telegramUsers()
    {
        return $this->belongsToMany(TelegramUser::class)
            ->withPivot('review_date', 'easiest_factor', 'correct_times');
    }
}
