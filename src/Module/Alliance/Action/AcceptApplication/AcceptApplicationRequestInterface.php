<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

interface AcceptApplicationRequestInterface
{
    public function getApplicationId(): int;
}
