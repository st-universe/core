<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

class Matchup
{
    private ShipWrapperInterface $attackingShipWrapper;

    /**
     * @var array<int, ShipWrapperInterface>
     */
    private array $targetShipWrappers;

    /**
     * @param array<int, ShipWrapperInterface> $targetShipWrappers
     */
    public function __construct(ShipWrapperInterface $attackingShipWrapper, array $targetShipWrappers)
    {
        $this->attackingShipWrapper = $attackingShipWrapper;
        $this->targetShipWrappers = $targetShipWrappers;
    }

    public function getAttacker(): ShipWrapperInterface
    {
        return $this->attackingShipWrapper;
    }

    /**
     * @return array<int, ShipWrapperInterface> $targetShipWrappers
     */
    public function getDefenders(): array
    {
        return $this->targetShipWrappers;
    }
}
