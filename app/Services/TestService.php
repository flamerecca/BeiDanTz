<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:47
 */

namespace App\Services;

use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;

class TestService implements TestServiceInterface
{

    /**
     * @return QuestionDTO
     */
    public function getQuestion(): QuestionDTO
    {
        return new QuestionDTO();
    }

    /**
     * @param AnswerDTO $answerDTO
     */
    public function setAnswer(AnswerDTO $answerDTO): void
    {
    }
}
