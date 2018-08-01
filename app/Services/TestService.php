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
        $vocabulary = $this->getVocabulary($telegramUser);

        $optionNumber = 4;
        $options = $this->getOptions($vocabulary, $optionNumber);

        return new QuestionDTO(
            $vocabulary->id,
            $vocabulary->content,
            $options,
            $vocabulary->answer
        );
    }

    /**
     * @param TelegramUser $telegramUser
     * @return Vocabulary
     */
    private function getVocabulary(TelegramUser $telegramUser): Vocabulary
    {
        $vocabularies = $this->vocabularyRepository
            ->getByCriteria(new TodayVocabulariesCriteria($telegramUser));

        if ($vocabularies->isEmpty()) {
            return $this->vocabularyRepository
                ->pushCriteria(new RandomCriteria())
                ->first();
        }

        return $vocabularies->random();
    }

    /**
     * @param Vocabulary $vocabulary
     * @param int $optionNumber
     * @return array
     */
    private function getOptions(Vocabulary $vocabulary, int $optionNumber): array
    {
        return $this->vocabularyRepository
            ->getByCriteria(new WrongAnswerCriteria($vocabulary, $optionNumber - 1))
            ->map(function ($vocabulary) {
                return $vocabulary->answer;
            })
            ->push($vocabulary->answer)
            ->shuffle()
            ->toArray();
    }

    /**
     * @param AnswerDTO $answerDTO
     */
    public function answer(AnswerDTO $answerDTO): void
    {
    }
}
