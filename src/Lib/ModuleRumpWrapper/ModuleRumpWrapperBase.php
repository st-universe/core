<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipRumpInterface;

abstract class ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    protected ShipWrapperInterface $wrapper;

    protected ShipRumpInterface $rump;

    /** @var array<int, BuildplanModuleInterface> */
    protected array $modules;

    /**
     * @param array<int, BuildplanModuleInterface> $modules
     */
    public function __construct(ShipWrapperInterface $wrapper, ShipRumpInterface $rump, array $modules)
    {
        $this->wrapper = $wrapper;
        $this->rump = $rump;
        $this->modules = $modules;
    }

    public function getModule(): iterable
    {
        return $this->modules;
    }
}
