<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeTorpedoType;

interface ChangeTorpedoTypeRequestInterface
{
    public function getTorpedoId(): int;
}
