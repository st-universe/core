<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

class BuildMenuWrapper
{
    /**
     * @param scalar $id
     */
    public function __get($id): BuildMenu
    {
        return new BuildMenu((int) $id);
    }
}
