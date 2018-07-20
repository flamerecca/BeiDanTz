<?php
/**
 * Created by PhpStorm.
 * User: reccachao
 * Date: 2018/7/21
 * Time: 00:16
 */

namespace App\DTO;

/**
 * 題目的資料結構，供給 bot render 考題
 * Class QuestionDTO
 * @package App\DTO
 */
class QuestionDTO
{
    /**
     * @var 題目單字物件
     */
    public $vocabulary;

    /**
     * @var 題目選項，為字串的陣列
     */
    public $options;

    /**
     * @var 解答，為選項 index
     */
    public $answer;
}
