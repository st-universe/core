<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\General\EntityWithHrefInterface;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;

interface EntityWithStorageInterface extends
    EntityWithCrewAssignmentsInterface,
    EntityWithInteractionCheckInterface,
    EntityWithHrefInterface
{
    public function getId(): int;

    public function getName(): string;

    /** @return Collection<int, Storage> Indexed by commodityId, ordered by commodityId */
    public function getStorage(): Collection;

    /** @return Collection<int, Storage> Indexed by commodityId, ordered by Commodity->sort */
    public function getBeamableStorage(): Collection;

    public function getStorageSum(): int;

    public function getMaxStorage(): int;

    public function getUser(): ?User;

    public function getLocation(): Map|StarSystemMap;

    public function getTransferEntityType(): TransferEntityTypeEnum;
}
