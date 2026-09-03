<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

interface ChangeNameRequestInterface
{
    public function getName(): string;
}
