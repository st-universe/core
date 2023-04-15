<?php

declare(strict_types=1);

namespace Stu\Component\Player;

final class UserCssEnum
{
    //RPG behavior
    public const CSS_BLACK = 'schwarz';
    public const CSS_PURPLE = 'lila';
    public const CSS_YELLOW = 'gelb';
    public const CSS_RED = 'rot';
    public const CSS_GREEN = 'grün';
    public const CSS_LCARS = 'lcars';


    public const CSS_CLASS = [
        self::CSS_BLACK => ['css' => self::CSS_BLACK, 'title' => 'Schwarz'],
        self::CSS_PURPLE => ['css' => self::CSS_PURPLE, 'title' => 'Lila'],
        self::CSS_YELLOW => ['css' => self::CSS_YELLOW, 'title' => 'Gelb'],
        self::CSS_RED => ['css' => self::CSS_RED, 'title' => 'Rot'],
        self::CSS_GREEN => ['css' => self::CSS_GREEN, 'title' => 'Grün'],
        self::CSS_LCARS => ['css' => self::CSS_LCARS, 'title' => 'LCars'],
    ];
}
