<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement;

use Stu\Lib\ShipManagement\Manager\ManagerInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class HandleManagers implements HandleManagersInterface
{
    /**
     * @var array<ManagerInterface>
     */
    private array $managers;

    /**
     * @param array<ManagerInterface> $managers
     */
    public function __construct(array $managers)
    {
        $this->managers = $managers;
    }

    public function handle(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];

        foreach ($this->managers as $manager) {
            $msg = array_merge($msg, $manager->manage($wrapper, $values, $managerProvider));
        }

        return $msg;
    }
}
