<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks\Colony;

interface ManualColonyTickRequestInterface
{
    public function getColonyId(): ?int;

    public function getGroupId(): ?int;
}
