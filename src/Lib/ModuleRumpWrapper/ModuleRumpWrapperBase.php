<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipRumpInterface;

abstract class ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    protected $modules;

    protected $rump;

    /**
     * @param ShipRumpInterface $rump
     * @param BuildplanModuleInterface[] $modules
     */
    public function __construct(ShipRumpInterface $rump, array $modules)
    {
        $this->modules = $modules;
        $this->rump = $rump;
    }

    public function getModule(): iterable
    {
        return $this->modules;
    }
}
