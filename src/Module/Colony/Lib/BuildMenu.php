<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildMenuEnum;

class BuildMenu
{
    private int $menuId;

    public function __construct(int $menuId)
    {
        $this->menuId = $menuId;
    }

    public function getName(): string
    {
        return BuildMenuEnum::getDescription($this->menuId);
    }
}
