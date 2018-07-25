<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/26
 * Time: 00:47
 */

namespace Tests\Unit;

use App\DTO\QuestionDTO;
use App\Entities\Vocabulary;
use Tests\TestCase;
use Mockery;

class QuestionDTOTest extends TestCase
{
    public function testGetVocabularyShouldReturnVocabulary()
    {
        $question = new QuestionDTO(
            Mockery::mock(Vocabulary::class),
            [],
            1
        );
        $this->assertInstanceOf(
            Vocabulary::class,
            $question->getVocabulary()
        );
    }

    public function testGetOptionsShouldReturnArray()
    {
        $question = new QuestionDTO(
            Mockery::mock(Vocabulary::class),
            [],
            1
        );
        $this->assertInternalType(
            'array',
            $question->getOptions()
        );
    }

    public function testGetAnswerShouldReturnInteger()
    {
        $question = new QuestionDTO(
            Mockery::mock(Vocabulary::class),
            [],
            1
        );
        $this->assertInternalType(
            'integer',
            $question->getAnswer()
        );
    }
}