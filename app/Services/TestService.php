<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:47
 */

namespace App\Services;

use App\Criteria\RandomCriteria;
use App\Criteria\TodayVocabulariesCriteria;
use App\Criteria\WrongAnswerCriteria;
use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;
use App\Repositories\TelegramUserRepository;
use App\Repositories\VocabularyRepository;

class TestService implements TestServiceInterface
{
    private $vocabularyRepository;
    private $telegramUserRepository;

    /**
     * TestService constructor.
     * @param VocabularyRepository $vocabularyRepository
     * @param TelegramUserRepository $telegramUserRepository
     */
    public function __construct(
        VocabularyRepository $vocabularyRepository,
        TelegramUserRepository $telegramUserRepository
    ) {
        $this->vocabularyRepository = $vocabularyRepository;
        $this->telegramUserRepository = $telegramUserRepository;
    }

    /**
     * @param TelegramUser $telegramUser
     * @return QuestionDTO
     */
    public function getQuestion(TelegramUser $telegramUser): QuestionDTO
    {
        // 找用戶是否有需要複習的單字
        $vocabularies = $this->vocabularyRepository
            ->getByCriteria(new TodayVocabulariesCriteria($telegramUser));
        
        if ($vocabularies->isEmpty()) {
            $vocabulary = $this->vocabularyRepository
                ->getByCriteria(new RandomCriteria())
                ->first();
        } else {
            $vocabulary = $vocabularies->random();
        }

        $wrongVocabularies = $this->vocabularyRepository
            ->getByCriteria(new WrongAnswerCriteria($vocabulary, 4));
        $options = [];
        for ($i = 0; $i < 4; $i++) {
            $options[$i] = $wrongVocabularies[$i]->answer;
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
