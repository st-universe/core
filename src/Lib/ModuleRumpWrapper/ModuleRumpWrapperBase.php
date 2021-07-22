<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipRumpInterface;

abstract class ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    protected $modules;

    protected $rump;

    protected $loggerUtil;

    /**
     * @param ShipRumpInterface $rump
     * @param BuildplanModuleInterface[] $modules
     */
    public function __construct(ShipRumpInterface $rump, array $modules, LoggerUtilInterface $loggerUtil = null)
    {
        if ($loggerUtil !== null) {
            $loggerUtil->log("__construct-ModuleRumpWrapperBase");
        }
        $this->modules = $modules;
        $this->rump = $rump;
        $this->loggerUtil = $loggerUtil;
    }

    public function getModule(): iterable
    {
        return $this->modules;
    }

    protected function doLog(): bool
    {
        return $this->loggerUtil !== null && $this->loggerUtil->doLog();
    }
}
