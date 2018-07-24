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
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;

class TestService implements TestServiceInterface
{

    /**
     * @param TelegramUser $telegramUser
     * @return QuestionDTO
     */
    public function getQuestion(TelegramUser $telegramUser): QuestionDTO
    {
        // 找用戶是否有需要複習的單字
        $vocabularies = $telegramUser->reviewingVocabularies()->get();

        if ($vocabularies->isEmpty()) {
            $vocabulary = Vocabulary::all()->random();
        } else {
            $vocabulary = $vocabularies->random();
        }
        $wrongVocabulary = Vocabulary::where('id', '!=', $vocabulary->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $options = [];
        for ($i = 0; $i < 4; $i++) {
            $options[$i] = $wrongVocabulary[$i]->answer;
        }

        $answer = rand(0, 3);
        $options[$answer] = $vocabulary->answer;


        return new QuestionDTO(
            $vocabulary,
            $options,
            $answer
        );
    }

    /**
     * @param AnswerDTO $answerDTO
     */
    public function answer(AnswerDTO $answerDTO): void
    {
    }
}
