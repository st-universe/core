<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineApplication;

interface DeclineApplicationRequestInterface
{
    public function getApplicationId(): int;
}
