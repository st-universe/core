<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\GiveUp;

interface GiveUpRequestInterface
{
    public function getColonyId(): int;
}
