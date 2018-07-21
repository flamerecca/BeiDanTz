<?php

namespace App\Http\Conversations;

use App\DTO\AnswerDTO;
use App\DTO\QuestionDTO;
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
     * @var TestServiceInterface
     */
    private $testService;

    /**
     *
     * @var QuestionDTO
     */
    private $question;

    /**
     * @var int
     */
    private $maxChance = 1;

    public function __construct(TestServiceInterface $testService)
    {
        $this->testService = $testService;
    }

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->question = $this->testService->getQuestion();
        $options = $this->question->getOptions();
        $buttons = collect($options)
            ->map(function ($option, $idx) {
                // each button value must be difference, otherwise telegram can't display template
                return Button::create($option)->value($idx);
            })
            ->push(Button::create('pass')->value('pass'))
            ->toArray();

        $vocabulary = $this->question->getVocabulary();
        $questionTemplate = Question::create($vocabulary->content)->addButtons($buttons);

        return $this->askQuestion($questionTemplate, $this->maxChance);
    }

    private function askQuestion(Question $questionTemplate, int $chance)
    {
        $this->ask($questionTemplate, function (Answer $answer) use ($questionTemplate, $chance) {
            if ($answer->isInteractiveMessageReply()) {
                $v = $answer->getValue();
                $correct = $v === $this->question->getAnswer();
                $correctAtOnce = $correct && $chance === $this->maxChance;
                $answerWrong = $v !== $this->question->getAnswer();
                $answerWrongOnce = $answerWrong && $chance === $this->maxChance;
                $pass = $v === 'pass';

                if ($correct || ($answerWrong && !$answerWrongOnce) || $pass) {
                    $this->bot->startConversation(new QuestionConversation($this->testService));
                } else {
                    $this->askQuestion($questionTemplate, $chance - 1);
                }
            }
        });
    }
}
