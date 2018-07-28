<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 01:15
 */

namespace App\Services;

/**
 * Class EasinessFactorService
 * @package App\Services
 */
class EasinessFactorService
{
    /**
     * 計算新的 Easiness Factor
     * @param string $easinessFactor
     * @param int $answerStatus
     * @return string
     */
    public function calculateNewEasinessFactor(string $easinessFactor, int $answerStatus):string
    {
        if (!is_numeric($easinessFactor)) {
            throw new \InvalidArgumentException('easiness factor is not numeric');
        }

        if (bccomp($easinessFactor, '999999999') === 1) {
            throw new \InvalidArgumentException('easiness factor is too big');
        }

        $change = (string)(0.1 - (5 - $answerStatus) * (0.08 + (5 - $answerStatus) * 0.02));
        $newEasinessFactor = bcadd($easinessFactor, $change, 3);

        if (bccomp($newEasinessFactor, '1.3') === -1) {
            return '1.300';
        }
        return $newEasinessFactor;
    }
}
