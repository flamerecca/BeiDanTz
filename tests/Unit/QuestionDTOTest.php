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
    /**
     * @var QuestionDTO
     */
    private $question;

    protected function setUp()
    {
        parent::setUp();
        $this->question = new QuestionDTO(1, 'test', ['選項', '測驗'], 1);
    }

    public function testGetVocabularyIdShouldReturnInteger()
    {
        $this->assertInternalType(
            'integer',
            $this->question->getVocabularyId()
        );
    }

    public function testGetContentShouldReturnString()
    {
        $this->assertInternalType(
            'string',
            $this->question->getContent()
        );
    }

    public function testGetOptionsShouldReturnArray()
    {
        $this->assertInternalType(
            'array',
            $this->question->getOptions()
        );
    }

    public function testGetAnswerShouldReturnInteger()
    {
        $this->assertInternalType(
            'string',
            $this->question->getAnswer()
        );
    }
}