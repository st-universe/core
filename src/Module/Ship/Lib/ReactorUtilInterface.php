<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;

interface ReactorUtilInterface
{
    /**
     * @param Collection<int, StorageInterface> $storages
     */
    public function storageContainsNeededCommodities(Collection $storages, ReactorWrapperInterface $reactor): bool;

    public function loadReactor(
        ShipInterface $ship,
        int $additionalLoad,
        ?ManagerProviderInterface $managerProvider,
        ReactorWrapperInterface $reactor
    ): ?string;
}
