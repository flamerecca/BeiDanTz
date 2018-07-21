<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:26
 */

namespace App\Services;

use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;

interface TestServiceInterface
{
    /**
     * 取得問題
     * @return QuestionDTO
     */
    public function getQuestion(): QuestionDTO;

    /**
     * 回傳答案狀況
     * @param AnswerDTO $answerDTO
     */
    public function answer(AnswerDTO $answerDTO): void;
}
