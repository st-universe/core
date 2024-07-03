<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

class WebEmitterSystemData extends AbstractSystemData
{
    public ?int $webUnderConstructionId = null;
    public ?int $ownedWebId = null;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        private TholianWebRepositoryInterface $tholianWebRepository
    ) {
        parent::__construct($shipSystemRepository);
    }

    #[Override]
    function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB;
    }

    public function getWebUnderConstruction(): ?TholianWebInterface
    {
        if ($this->webUnderConstructionId === null) {
            return null;
        }
        return $this->tholianWebRepository->find($this->webUnderConstructionId);
    }

    public function getOwnedTholianWeb(): ?TholianWebInterface
    {
        if ($this->ownedWebId === null) {
            return null;
        }

        return $this->tholianWebRepository->find($this->ownedWebId);
    }

    public function setWebUnderConstructionId(?int $webId): WebEmitterSystemData
    {
        $this->webUnderConstructionId = $webId;
        return $this;
    }

    public function setOwnedWebId(?int $webId): WebEmitterSystemData
    {
        $this->ownedWebId = $webId;
        return $this;
    }

    public function getCooldown(): ?int
    {
        return $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB)->getCooldown();
    }

    public function isUseable(): bool
    {
        if ($this->webUnderConstructionId !== null) {
            return false;
        }

        $cooldown = $this->getCooldown();

        return $cooldown === null ? true : $cooldown < time();
    }
}
