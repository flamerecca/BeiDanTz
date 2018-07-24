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
use App\Entities\TelegramUser;

interface TestServiceInterface
{
    /**
     * 取得問題
     * @param TelegramUser $telegramUser
     * @return QuestionDTO
     */
    public function getQuestion(TelegramUser $telegramUser): QuestionDTO;

    /**
     * 回傳答案狀況
     * @param AnswerDTO $answerDTO
     */
    public function answer(AnswerDTO $answerDTO): void;
}
