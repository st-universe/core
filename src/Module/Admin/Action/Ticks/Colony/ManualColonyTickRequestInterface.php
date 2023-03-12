<?php

namespace Stu\Module\Admin\Action\Ticks\Colony;

interface ManualColonyTickRequestInterface
{
    public function getColonyId(): ?int;

    public function getGroupId(): ?int;
}
