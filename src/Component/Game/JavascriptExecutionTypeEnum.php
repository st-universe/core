<?php

declare(strict_types=1);

namespace Stu\Component\Game;

enum JavascriptExecutionTypeEnum: int
{
    case BEFORE_RENDER = 1;
    case AFTER_RENDER = 2;
    case ON_AJAX_UPDATE = 3;
}
