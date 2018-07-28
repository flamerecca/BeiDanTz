<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 01:37
 */

namespace Tests\Unit;

use App\Services\EasinessFactorService;
use Tests\TestCase;

class EasinessFactorServiceTest extends TestCase
{
    private $easinessFactorService;

    protected function setUp()
    {
        $this->easinessFactorService = new EasinessFactorService();
    }

    public function testNewEasinessFactorIsCorrect(): void
    {
        $newEasinessFactor = $this->easinessFactorService->calculateNewEasinessFactor('2.500', 0);
        $this->assertEquals('1.700', $newEasinessFactor);
    }

    public function testMinimumNewEasinessFactorIsOnePointThree()
    {
        $newEasinessFactor = $this->easinessFactorService->calculateNewEasinessFactor('0', 0);
        $this->assertEquals('1.300', $newEasinessFactor);
    }

    public function testIfOldEasinessFactorIsNotStringShouldThrowException()
    {
        $this->expectException(\TypeError::class);
        $this->easinessFactorService->calculateNewEasinessFactor( [], 0);
    }

    public function testIfOldEasinessFactorIsNotNumericShouldThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('easiness factor is not numeric');

        $this->easinessFactorService->calculateNewEasinessFactor('aaa', 0);
    }

    public function testIfAnswerStatusIsNotNumberShouldThrowException()
    {
        $this->expectException(\TypeError::class);
        $this->easinessFactorService->calculateNewEasinessFactor('0', 'aa');
    }

    public function testIfOldEasinessFactorIsTooBig()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('easiness factor is too big');

        $this->easinessFactorService->calculateNewEasinessFactor(
            '99999999999999999999999999',
            0
        );
    }
}