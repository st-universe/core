<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\General\EntityWithHrefInterface;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;

interface EntityWithStorageInterface extends
    EntityWithCrewAssignmentsInterface,
    EntityWithInteractionCheckInterface,
    EntityWithHrefInterface
{
    public function getId(): int;

    public function getName(): string;

    /** @return Collection<int, StorageInterface> Indexed by commodityId, ordered by commodityId */
    public function getStorage(): Collection;

    /** @return Collection<int, StorageInterface> Indexed by commodityId, ordered by Commodity->sort */
    public function getBeamableStorage(): Collection;

    public function getStorageSum(): int;

    public function getMaxStorage(): int;

    public function getUser(): ?UserInterface;

    public function getLocation(): MapInterface|StarSystemMapInterface;

    public function getTransferEntityType(): TransferEntityTypeEnum;
}
