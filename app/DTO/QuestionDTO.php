<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:16
 */

namespace App\DTO;

use App\Entities\Vocabulary;

/**
 * 題目的資料結構，供給 bot render 考題
 * Class QuestionDTO
 * @package App\DTO
 */
class QuestionDTO
{
    /**
     * @var Vocabulary 題目單字物件
     */
    private $vocabulary;

    /**
     * @var array 題目選項，為字串的陣列
     */
    private $options;

    /**
     * @var int 解答，為選項 index
     */
    private $answer;

    public function __construct(Vocabulary $vocabulary, array $options, int $answer)
    {
        $this->vocabulary = $vocabulary;
        $this->options = $options;
        $this->answer = $answer;
    }

    /**
     * @return Vocabulary
     */
    public function getVocabulary(): Vocabulary
    {
        return $this->vocabulary;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return int
     */
    public function getAnswer(): int
    {
        return $this->answer;
    }
}
