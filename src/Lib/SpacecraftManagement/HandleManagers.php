<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement;

use Override;
use Stu\Component\Borg\BorgAssimilationActionGuard;
use Stu\Lib\SpacecraftManagement\Manager\ManagerInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
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
        BorgAssimilationActionGuard::assertUsable($wrapper->get());
        $msg = [];

        foreach ($this->managers as $manager) {
            $msg = array_merge($msg, $manager->manage($wrapper, $values, $managerProvider));
        }

        return $msg;
    }
}
