<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:16
 */

namespace App\DTO;

/**
 * 題目的資料結構，供給 bot render 考題
 * Class QuestionDTO
 * @package App\DTO
 * @codeCoverageIgnore
 */
class QuestionDTO
{
    /**
     * @var int
     */
    private $vocabularyId;

    /**
     * @var string
     */
    private $content;

    /**
     * @var array 題目選項，為字串的陣列
     */
    private $options;

    /**
     * @var int 解答，為選項 index
     */
    private $answer;

    public function __construct(int $vocabularyId, string $content, array $options, int $answer)
    {
        $this->vocabularyId = $vocabularyId;
        $this->content = $content;
        $this->options = $options;
        $this->answer = $answer;
    }

    /**
     * @return int
     */
    public function getVocabularyId(): int
    {
        return $this->vocabularyId;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
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
