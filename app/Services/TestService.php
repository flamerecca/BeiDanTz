<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:47
 */

namespace App\Services;

use App\Criteria\LimitCriteria;
use App\Criteria\NewVocabulariesCriteria;
use App\Criteria\PastVocabulariesCriteria;
use App\Criteria\RandomCriteria;
use App\Criteria\TodayVocabulariesCriteria;
use App\Criteria\DifferentVocabularyCriteria;
use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;
use App\Repositories\TelegramUserRepository;
use App\Repositories\VocabularyRepository;

/**
 * 處理題目的 service
 * Class TestService
 * @package App\Services
 */
class TestService implements TestServiceInterface
{
    private $vocabularyRepository;
    private $telegramUserRepository;
    private $easinessFactorService;

    /**
     * TestService constructor.
     * @param VocabularyRepository $vocabularyRepository
     * @param TelegramUserRepository $telegramUserRepository
     * @param EasinessFactorService $easinessFactorService
     */
    public function __construct(
        VocabularyRepository $vocabularyRepository,
        TelegramUserRepository $telegramUserRepository,
        EasinessFactorService $easinessFactorService
    ) {
        $this->vocabularyRepository = $vocabularyRepository;
        $this->telegramUserRepository = $telegramUserRepository;
        $this->easinessFactorService = $easinessFactorService;
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
     * 取得準備複習單字
     * @param TelegramUser $telegramUser
     * @return Vocabulary
     */
    private function getVocabulary(TelegramUser $telegramUser): Vocabulary
    {
        // 找用戶是否有昨天之前需要複習的單字
        $vocabularies = $this->vocabularyRepository
            ->getByCriteria(new PastVocabulariesCriteria($telegramUser));

        if ($vocabularies->isNotEmpty()) {
            return $vocabularies->random();
        }

        // 找用戶是否有今天需要複習的單字
        $vocabularies = $this->vocabularyRepository
            ->getByCriteria(new TodayVocabulariesCriteria($telegramUser));

        if ($vocabularies->isNotEmpty()) {
            return $vocabularies->random();
        }

        return $this->vocabularyRepository
            ->pushCriteria(new NewVocabulariesCriteria($telegramUser))
            ->pushCriteria(new RandomCriteria())
            ->first();
    }

    /**
     * @param Vocabulary $vocabulary
     * @param int $optionNumber
     * @return array
     */
    private function getOptions(Vocabulary $vocabulary, int $optionNumber): array
    {
        $wrongAnswer = $this->vocabularyRepository
            ->pushCriteria(new DifferentVocabularyCriteria($vocabulary))
            ->pushCriteria(new RandomCriteria())
            ->pushCriteria(new LimitCriteria($optionNumber - 1))
            ->all();

        return collect($wrongAnswer)
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
        $answeringStatus = $answerDTO->getAnsweringStatus();
        $telegramUser = TelegramUser::where('telegram_id', $answerDTO->getUserId())->first();
        $originVocabulary = Vocabulary::find($answerDTO->getVocabularyId());
        $vocabulary = $telegramUser->vocabularies()
            ->where('vocabulary_id', $answerDTO->getVocabularyId())
            ->first();

        $continuingCorrectTimes = $this->getContinuingCorrectTimes($vocabulary, $answeringStatus);
        $originEasiestFactor = $this->getOriginEasiestFactor($vocabulary, $originVocabulary);

        $reviewDate = $this->getReviewDate($originEasiestFactor, $continuingCorrectTimes);
        $newEasinessFactor = $this->easinessFactorService->calculateNewEasinessFactor(
            $originEasiestFactor,
            $answeringStatus
        );

        $telegramUser->vocabularies()->syncWithoutDetaching([
            $answerDTO->getVocabularyId() => [
                'review_date' => $reviewDate->format('Y-m-d'),
                'easiest_factor' => $newEasinessFactor,
                'continuing_correct_times' => $continuingCorrectTimes,
            ]
        ]);
    }

    /**
     * @param Vocabulary|null $vocabulary
     * @param int $answeringStatus
     * @return int
     */
    private function getContinuingCorrectTimes(?Vocabulary $vocabulary, int $answeringStatus): int
    {
        if (is_null($vocabulary)) {
            $continuingCorrectTimes = 0;
        } else {
            $continuingCorrectTimes = $vocabulary->pivot->continuing_correct_times;
        }

        if ($this->isRightAnswer($answeringStatus)) {
            $continuingCorrectTimes++;
        } else {
            $continuingCorrectTimes = 0;
        }

        return $continuingCorrectTimes;
    }

    /**
     * @param Vocabulary|null $vocabulary
     * @param Vocabulary $originVocabulary
     * @return string
     */
    private function getOriginEasiestFactor(?Vocabulary $vocabulary, Vocabulary $originVocabulary): string
    {
        if (is_null($vocabulary)) {
            return $originVocabulary->easiest_factor;
        } else {
            return $vocabulary->pivot->easiest_factor;
        }
    }

    /**
     * 根據 $answeringStatus 判斷回答是否正確
     * @param int $answeringStatus
     * @return bool
     */
    private function isRightAnswer(int $answeringStatus): bool
    {
        if ($answeringStatus === AnswerDTO::PASS
            || $answeringStatus === AnswerDTO::WRONG_TWICE
            || $answeringStatus === AnswerDTO::WRONG_ONCE
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 根據 $easiestFactor 和 $correctTimes 判斷下次複習時間
     * @param string $easiestFactor
     * @param int $correctTimes
     * @return \DateTime
     */
    private function getReviewDate(string $easiestFactor, int $correctTimes): \DateTime
    {
        $interval = 3;

        if ($correctTimes === 0) {
            return new \DateTime('tomorrow');
        }

        for ($i = 0; $i < $correctTimes - 1; $i++) {
            $interval = bcmul($interval, $easiestFactor, 3);
        }
        // 無條件捨去
        $interval = bcmul($interval, '1', 0);
        return new \DateTime('today + ' . $interval . ' day');
    }
}
