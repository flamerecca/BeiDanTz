<?php

namespace Tests\Unit;

use App\Criteria\NewVocabulariesCriteria;
use App\Entities\TelegramUser;
use App\Entities\Vocabulary;
use App\Repositories\VocabularyRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewVocabulariesCriteriaTest extends TestCase
{
    use RefreshDatabase;
    private $telegramUser;
    private $otherTelegramUser;

    public function setUp()
    {
        parent::setUp();
        Vocabulary::create([
            'content' => 'apple',
            'answer' => '蘋果',
            'easiest_factor' => 2.5
        ]);
        Vocabulary::create([
            'content' => 'bee',
            'answer' => '蜜蜂',
            'easiest_factor' => 2.5
        ]);

        Vocabulary::create([
            'content' => 'cat',
            'answer' => '貓',
            'easiest_factor' => 2.5
        ]);


        $this->telegramUser = TelegramUser::create([
            'telegram_id' => '1'
        ]);

        $this->otherTelegramUser = TelegramUser::create([
            'telegram_id' => '2'
        ]);
    }

    public function testIfVocabulariesWillBeReviewInFutureShouldNotBeSelect()
    {
        $vocabularyId = 1;

        $this->telegramUser->vocabularies()->sync([
            $vocabularyId => [
                'review_date' =>  (new \DateTime('today + 1 day'))->format('Y-m-d'),
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);

        $repository = app()->make(VocabularyRepository::class);
        $newVocabulary = $repository->getByCriteria(
            new NewVocabulariesCriteria($this->telegramUser)
        );

        $this->assertNotEquals($newVocabulary->first()->id, $vocabularyId);
    }

    public function testIfUserDoesNotHadReviewedVocabulariesShouldSelectNewVocabulary()
    {
        $repository = app()->make(VocabularyRepository::class);
        $newVocabulary = $repository->getByCriteria(
            new NewVocabulariesCriteria($this->telegramUser)
        );
        $this->assertNotNull($newVocabulary->first());
    }

    public function testIfOtherUserVocabularyShouldNotAffectCurrentUserVocabulary()
    {
        $vocabularyId = 1;

        $this->otherTelegramUser->vocabularies()->attach([
            $vocabularyId => [
                'review_date' =>  (new \DateTime('today + 1 day'))->format('Y-m-d'),
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);
        $this->otherTelegramUser->vocabularies()->attach([
            2 => [
                'review_date' =>  (new \DateTime('today + 1 day'))->format('Y-m-d'),
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);
        $this->otherTelegramUser->vocabularies()->attach([
            3 => [
                'review_date' =>  (new \DateTime('today + 1 day'))->format('Y-m-d'),
                'easiest_factor' => 3.000,
                'continuing_correct_times' => 5,
            ]
        ]);

        $repository = app()->make(VocabularyRepository::class);

        $newVocabulary = $repository->getByCriteria(
            new NewVocabulariesCriteria($this->telegramUser)
        );
        $this->assertEquals($vocabularyId, $newVocabulary->first()->id);
    }
}
