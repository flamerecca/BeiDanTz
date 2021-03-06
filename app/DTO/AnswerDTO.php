<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:21
 */

namespace App\DTO;

/**
 * 答題狀況的資料格式
 * Class AnswerDTO
 * @package App\DTO
 * @codeCoverageIgnore
 */
class AnswerDTO
{
    /**
     * @var int 回答的用戶 ID
     */
    private $userId;

    /**
     * @var int 回答的單字 ID
     */
    private $vocabularyId;

    /**
     * @var int 回答狀況，應為 0-5 之整數
     */
    private $answeringStatus;

    const PASS = 0;
    const WRONG_TWICE = 1;
    const WRONG_ONCE = 2;
    const CORRECT_OVER_MAX_TIME = 3;
    const CORRECT_BETWEEN_MIN_MAX_TIME = 4;
    const CORRECT_LESS_MIN_TIME = 5;

    public function __construct(int $userId, int $vocabularyId, int $status)
    {
        $this->userId = $userId;
        $this->vocabularyId = $vocabularyId;
        $this->answeringStatus = $status;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getVocabularyId(): int
    {
        return $this->vocabularyId;
    }

    /**
     * @return int
     */
    public function getAnsweringStatus(): int
    {
        return $this->answeringStatus;
    }
}
