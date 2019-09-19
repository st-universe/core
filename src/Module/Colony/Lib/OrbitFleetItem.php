<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Repository\FleetRepositoryInterface;

final class OrbitFleetItem implements OrbitFleetItemInterface
{
    private $fleetId;

    private $shipList;

    private $userId;

    public function __construct(
        int $fleetId,
        array $shipList,
        int $userId
    ) {
        $this->userId = $userId;
        $this->shipList = $shipList;
        $this->fleetId = $fleetId;
    }

    public function getName(): string
    {
        if ($this->fleetId == 0) {
            return _('Einzelschiffe');
        }

        // @todo refactor
        global $container;
        $fleetRepo = $container->get(FleetRepositoryInterface::class);

        return $fleetRepo->find($this->fleetId)->getName();
    }

    public function getShips(): array
    {
        return $this->shipList;
    }
}