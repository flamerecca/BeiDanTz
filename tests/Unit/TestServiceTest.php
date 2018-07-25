<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/24
 * Time: 23:10
 */

namespace Tests\Unit;

use App\DTO\QuestionDTO;
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;
use App\Repositories\TelegramUserRepository;
use App\Repositories\TelegramUserRepositoryEloquent;
use App\Repositories\VocabularyRepository;
use App\Repositories\VocabularyRepositoryEloquent;
use App\Services\TestService;
use Mockery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TestServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        $vocabulary = Vocabulary::create([
            'content' => 'apple',
            'answer' => '蘋果',
            'easiest_factor' => 2.5
        ]);

        TelegramUser::create([
            'id' => 1,
            'telegram_id' => '1'
        ]);

        $vocabulary->telegramUsers()->attach(
            1,
            [
                'review_date' => '2018-07-25',
                'easiest_factor' => 0
            ]
        );

    }

    public function testGetQuestionShouldReturnQuestionDTO()
    {
        $testService = new TestService(
            new VocabularyRepositoryEloquent(app()),
            new TelegramUserRepositoryEloquent(app())
        );
        $telegramUser = new TelegramUser();
        $telegramUser->id = 1;
        $this->assertInstanceOf(QuestionDTO::class, $testService->getQuestion($telegramUser));
    }
}