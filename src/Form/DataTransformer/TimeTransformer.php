<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-21
 * Time: 18:48
 */
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManagerInterface;

class TimeTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct()
    {

    }

    public function transform($integerTime)
    {
        if (null === $integerTime || $integerTime === '') {
            return '';
        }

        $hours = intdiv($integerTime, 60);
        $minutes = $integerTime % 60;
        if($minutes === 0)
            $minutes = "00";

        return $hours .":".$minutes;
    }

    public function reverseTransform($textTime)
    {
        if (!$textTime || $textTime === '') {
            return null;
        }

        $timeArray = explode(":", $textTime);
        $hours = $timeArray[0];
        $minutes = $timeArray[1];

        return $hours * 60 + $minutes;

    }
}