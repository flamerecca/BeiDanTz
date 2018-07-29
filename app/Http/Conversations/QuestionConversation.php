<?php

namespace App\Http\Conversations;

use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;
use App\Services\TestService;
use App\Services\UserService;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class QuestionConversation extends Conversation
{
    /**
     * @var QuestionDTO
     */
    protected $question;

    /**
     * @var Question
     */
    protected $template;

    /**
     * @var int
     */
    protected $wrongTimes = 0;

    /**
     * 最大能錯誤的次數，超過這個次數後進入到下一題
     * @var int
     */
    private $maxWrongTimes = 1;

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
        $this->question = $testService->getQuestion($user);

        $options = $this->question->getOptions();
        $buttons = collect($options)
            ->map(function ($option, $idx) {
                // each button value must be difference, otherwise telegram can't display template
                return Button::create($option)->value($idx);
            })
            ->push(Button::create('pass')->value('pass'))
            ->toArray();

        $vocabulary = $this->question->getVocabulary();
        $this->template = Question::create($vocabulary->content)->addButtons($buttons);

        return $this->askQuestion();
    }

    private function askQuestion()
    {
        $startAskingTime = microtime(true);

        $this->ask($this->template, function (Answer $answer) use ($startAskingTime) {
            if ($answer->isInteractiveMessageReply()) {
                $answerTime = $this->calculateAnswerTime($startAskingTime);
                $v = $answer->getValue();
                $correct = $v == $this->question->getAnswer();
                $pass = $v === 'pass';
                $this->wrongTimes += !$correct;

                $this->replyAnswerStatus($pass, $correct);

                $toNextQuestion = $correct || $this->wrongTimes > $this->maxWrongTimes || $pass;
                if ($toNextQuestion) {
                    $this->nextQuestion($pass, $answerTime);
                } else {
                    $this->askQuestion();
                }
            }
        });
    }

    private function calculateAnswerTime($startAskingTime): float
    {
        // convert microsecond to millisecond, because ANSWER_MIN/MAX_TIME is set by millisecond
        return (microtime(true) - $startAskingTime) * 1000;
    }

    private function replyAnswerStatus(bool $pass, bool $correct): void
    {
        if ($pass) {
            $this->say('跳過');
        } elseif ($correct) {
            $this->say('答對惹');
        } else {
            $this->say('答錯惹');
        }
    }

    private function nextQuestion($pass, $answerTime): void
    {
        $status = $this->calculateAnsweringStatus($pass, $answerTime);

        $dto = new AnswerDTO(
            $this->bot->getUser()->getId(),
            $this->question->getVocabulary()->id,
            $status
        );
        $service = app()->make(TestService::class);
        $service->answer($dto);
        $this->bot->startConversation(new QuestionConversation());
    }


    private function calculateAnsweringStatus(bool $isPass, float $answerTime): int
    {
        if ($isPass) {
            return AnswerDTO::PASS;
        } elseif ($this->wrongTimes === 0) {
            $min = config('botman.config.answer_min_time');
            $max = config('botman.config.answer_max_time');
            if ($answerTime < $min) {
                return AnswerDTO::CORRECT_LESS_MIN_TIME;
            } elseif ($answerTime >= $min && $answerTime < $max) {
                return AnswerDTO::CORRECT_BETWEEN_MIN_MAX_TIME;
            }
            return AnswerDTO::CORRECT_OVER_MAX_TIME;
        } elseif ($this->wrongTimes == 1) {
            return AnswerDTO::WRONG_ONCE;
        }
        return AnswerDTO::WRONG_TWICE;
    }

    /**
     * execute before run, use to decide skip or not
     * if you want to know more detail, please visit \BotMan\BotMan\Traits\HandlesConversations
     *
     * @param IncomingMessage $message
     * @return bool
     */
    public function skipsConversation(IncomingMessage $message): bool
    {
        if ($message->getExtras('skip')) {
            return true;
        } else {
            return false;
        }
    }
}
