<?php

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;

interface ReactorUtilInterface
{
    /**
     * @param Collection<int, Storage> $storages
     */
    public function storageContainsNeededCommodities(Collection $storages, ReactorWrapperInterface $reactor): bool;

    public function loadReactor(
        Spacecraft $spacecraft,
        int $additionalLoad,
        ?ManagerProviderInterface $managerProvider,
        ReactorWrapperInterface $reactor
    ): ?string;
}
