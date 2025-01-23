<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

class WebEmitterSystemData extends AbstractSystemData
{
    public ?int $webUnderConstructionId = null;
    public ?int $ownedWebId = null;

    public function __construct(
        SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private TholianWebRepositoryInterface $tholianWebRepository,
        StatusBarFactoryInterface $statusBarFactory
    ) {
        parent::__construct($shipSystemRepository, $statusBarFactory);
    }

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::THOLIAN_WEB;
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
        return $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::THOLIAN_WEB)->getCooldown();
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
