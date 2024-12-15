<?php

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StorageInterface;

interface ReactorUtilInterface
{
    /**
     * @param Collection<int, StorageInterface> $storages
     */
    public function storageContainsNeededCommodities(Collection $storages, ReactorWrapperInterface $reactor): bool;

    public function loadReactor(
        SpacecraftInterface $spacecraft,
        int $additionalLoad,
        ?ManagerProviderInterface $managerProvider,
        ReactorWrapperInterface $reactor
    ): ?string;
}
