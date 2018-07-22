<?php

namespace App\Http\Conversations;

use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;
use App\Services\TestService;
use App\Services\TestServiceInterface;
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
     * @var int
     */
    private $maxChance = 1;

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $service = app()->make(TestService::class);
        $questionDTO = $service->getQuestion();
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

        return $this->askQuestion($questionTemplate, $this->maxChance, $questionDTO);
    }

    private function askQuestion(Question $questionTemplate, int $chance, QuestionDTO $question)
    {
        $startAskingTime = microtime(true);
        $this->ask($questionTemplate, function (Answer $answer) use ($questionTemplate, $chance, $startAskingTime, $question) {
            if ($answer->isInteractiveMessageReply()) {
                // change microtime to millisecond
                $answerTime = (microtime(true) - $startAskingTime) * 1000;
                $v = $answer->getValue();
                $correct = $v === $question->getAnswer();
                $firstAnswer = $chance === $this->maxChance;
                $pass = $v === 'pass';

                if ($correct || !$firstAnswer || $pass) {
                    $status = $this->getAnsweringStatus($pass, $answerTime, $firstAnswer, $correct);
                    $dto = new AnswerDTO(
                        $this->bot->getUser()->getId(),
                        $question->getVocabulary()->id,
                        $status
                    );
                    $service = app()->make(TestService::class);
                    $service->answer($dto);
                    $this->bot->startConversation(new QuestionConversation());
                } else {
                    $this->askQuestion($questionTemplate, $chance - 1, $question);
                }
            }
        });
    }

    private function getAnsweringStatus($pass, $answerTime, $firstAnswer, $correct): int
    {
        if ($pass) return AnswerDTO::PASS;
        if ($correct && $firstAnswer) {
            $min = config('botman.config.answer_min_time');
            $max = config('botman.config.answer_max_time');
            if ($answerTime < $min) {
                return AnswerDTO::CORRECT_LESS_MIN_TIME;
            } elseif ($answerTime >= $min && $answerTime < $max) {
                return AnswerDTO::CORRECT_BETWEEN_MIN_MAX_TIME;
            }
            return AnswerDTO::CORRECT_OVER_MAX_TIME;
        }
        if ($correct && !$firstAnswer) return AnswerDTO::WRONG_ONCE;

        return AnswerDTO::WRONG_TWICE;
    }
}
