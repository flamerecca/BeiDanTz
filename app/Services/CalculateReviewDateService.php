<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/27
 * Time: 23:33
 */

namespace App\Services;


use App\DTO\AnswerDTO;

class CalculateReviewDateService
{
    public function calculateReviewDate(AnswerDTO $answerDTO): \DateTime
    {
        // 如果回答錯誤，回傳明天日期

        // 如果回答正確，取出回答正確次數

        //
    }
}