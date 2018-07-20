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
 */
class AnswerDTO
{
    /**
     * @var 回答的用戶 ID
     */
    public $userId;

    /**
     * @var 回答的單字 ID
     */
    public $vocabularyID;

    /**
     * @var 回答狀況，應為 0-5 之整數
     */
    public $answeringStatus;
}
