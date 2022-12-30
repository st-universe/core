<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

class WebEmitterSystemData extends AbstractSystemData
{
    public ?int $webId = null;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private TholianWebRepositoryInterface $tholianWebRepository;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        TholianWebRepositoryInterface $tholianWebRepository
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->tholianWebRepository = $tholianWebRepository;
    }

    public function update(): void
    {
        $this->updateSystemData(
            ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getTholianWeb(): ?TholianWebInterface
    {
        if ($this->webId === null) {
            return null;
        }

        return $this->tholianWebRepository->find($this->webId);
    }

    public function setTholianWebId(?int $webId): WebEmitterSystemData
    {
        $this->webId = $webId;
        return $this;
    }

    public function isUseable(): bool
    {
        if ($this->webId !== null) {
            return false;
        }

        $cooldown = $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB)->getCooldown();

        return $cooldown === null ? true : $cooldown < time();
    }
}
