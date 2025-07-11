<?php

declare(strict_types=1);

namespace Stu\Module\Template;

enum StatusBarColorEnum: string
{
    case EMPTY = '';
    case YELLOW = 'aaaa00';
    case GREEN = '00aa00';
    case GREY = '777777';
    case RED = 'ff0000';
    case BLUE = '0070cf';
    case DARKBLUE = '004682';

        // shield bar
    case SHIELD_ON = '00fbff';
    case SHIELD_OFF = '004aff';
}
