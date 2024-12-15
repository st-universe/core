<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement;

use Override;
use Stu\Lib\ShipManagement\Manager\ManagerInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class HandleManagers implements HandleManagersInterface
{
    /**
     * @param array<ManagerInterface> $managers
     */
    public function __construct(private array $managers) {}

    #[Override]
    public function handle(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];

        foreach ($this->managers as $manager) {
            $msg = array_merge($msg, $manager->manage($wrapper, $values, $managerProvider));
        }

        return $msg;
    }
}
