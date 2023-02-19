<?php

namespace Stu\Component\Ship\UpdateLocation\Handler;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface UpdateLocationHandlerInterface
{
    public function handle(ShipWrapperInterface $wrapper, ?ShipInterface $tractoringShip): void;

    public function clearMessages(): void;

    /**
     * @return list<string>
     */
    public function getInternalMsg(): array;
}
