<?php

namespace App\Http\Conversations;

use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;
use App\Services\TestService;
use App\Services\TestServiceInterface;
use App\Services\UserService;
use BotMan\BotMan\Interfaces\UserInterface;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Doctrine\DBAL\Types\ConversionException;
use Illuminate\Support\Facades\Log;

class QuestionConversation extends Conversation
{
    /**
     * 最大能錯誤的次數，超過這個次數後進入到下一題
     * @var int
     */
    private $maxWroungTimes = 1;

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $testService = app()->make(TestService::class);
        $userService = app()->make(UserService::class);
        $userId = $this->bot->getUser()->getId();
        $user = $userService->fistOrCreateUser($userId);
        $questionDTO = $testService->getQuestion($user);
        $options = $questionDTO->getOptions();
        $buttons = collect($options)
            ->map(function ($option, $idx) {
                // each button value must be difference, otherwise telegram can't display template
                return Button::create($option)->value($idx);
            })
            ->push(Button::create('pass')->value('pass'))
            ->toArray();

        $vocabulary = $questionDTO->getVocabulary();
        $questionTemplate = Question::create($vocabulary->content)->addButtons($buttons);

        return $this->askQuestion($questionTemplate, 0, $questionDTO);
    }

    private function askQuestion(Question $questionTemplate, int $wrongTimes, QuestionDTO $question)
    {
        $startAskingTime = microtime(true);
        $this->ask(
            $questionTemplate,
            function (Answer $answer) use (
                $questionTemplate,
                $wrongTimes,
                $startAskingTime,
                $question
            ) {
                if ($answer->isInteractiveMessageReply()) {
                    // convert microsecond to millisecond, because ANSWER_MIN/MAX_TIME is set by millisecond
                    $answerTime = (microtime(true) - $startAskingTime) * 1000;
                    $v = $answer->getValue();
                    $correct = $v == $question->getAnswer();
                    $pass = $v === 'pass';
                    $wrongTimes += !$correct;

                    if ($pass) {
                        $this->say('跳過');
                    } elseif ($correct) {
                        $this->say('答對惹');
                    } else {
                        $this->say('答錯惹');
                    }


                    if ($correct || $wrongTimes > $this->maxWroungTimes || $pass) {
                        $status = $this->calculateAnsweringStatus($pass, $wrongTimes, $answerTime);

                        $dto = new AnswerDTO(
                            $this->bot->getUser()->getId(),
                            $question->getVocabulary()->id,
                            $status
                        );
                        $service = app()->make(TestService::class);
                        $service->answer($dto);
                        $this->bot->startConversation(new QuestionConversation());
                    } else {
                        $this->askQuestion($questionTemplate, $wrongTimes, $question);
                    }
                }
            }
        );
    }

    private function calculateAnsweringStatus(bool $isPass, int $wrongTimes, float $answerTime): int
    {
        if ($isPass) {
            return AnswerDTO::PASS;
        }
        if ($wrongTimes == 0) {
            $min = config('botman.config.answer_min_time');
            $max = config('botman.config.answer_max_time');
            if ($answerTime < $min) {
                return AnswerDTO::CORRECT_LESS_MIN_TIME;
            }
            if ($answerTime >= $min && $answerTime < $max) {
                return AnswerDTO::CORRECT_BETWEEN_MIN_MAX_TIME;
            }

            return AnswerDTO::CORRECT_OVER_MAX_TIME;
        }
        if ($wrongTimes == 1) {
            return AnswerDTO::WRONG_ONCE;
        }
        return AnswerDTO::WRONG_TWICE;
    }
}
