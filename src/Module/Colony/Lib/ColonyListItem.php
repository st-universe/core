<?php

// @todo enable strict typing
declare(strict_types=0);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetTypeInterface;
use Stu\Orm\Entity\StarSystemInterface;

final class ColonyListItem implements ColonyListItemInterface
{
    private ColonyInterface $colony;

    private $signatureCount;

    private CommodityConsumptionInterface $commodityConsumption;

    public function __construct(
        CommodityConsumptionInterface $commodityConsumption,
        ColonyInterface $colony,
        $signatureCount
    ) {
        $this->commodityConsumption = $commodityConsumption;
        $this->colony = $colony;
        $this->signatureCount = $signatureCount;
    }

    public function getId(): int
    {
        return $this->colony->getId();
    }

    public function getName(): string
    {
        return $this->colony->getName();
    }

    public function getSystem(): StarSystemInterface
    {
        return $this->colony->getSystem();
    }

    public function getSX(): int
    {
        return $this->colony->getSX();
    }

    public function getSY(): int
    {
        return $this->colony->getSY();
    }

    public function getSignatureCount(): int
    {
        return $this->signatureCount;
    }

    public function getPopulation(): int
    {
        return $this->colony->getPopulation();
    }

    public function getHousing(): int
    {
        return $this->colony->getMaxBev();
    }

    public function getImmigration(): int
    {
        return $this->colony->getImmigration();
    }

    public function getEps(): int
    {
        return $this->colony->getEps();
    }

    public function getMaxEps(): int
    {
        return $this->colony->getMaxEps();
    }

    public function getEpsProduction(): int
    {
        return $this->colony->getEpsProduction();
    }

    public function getStorageSum(): int
    {
        return $this->colony->getStorageSum();
    }

    public function getMaxStorage(): int
    {
        return $this->colony->getMaxStorage();
    }

    public function getStorage(): Collection
    {
        return $this->colony->getStorage();
    }

    public function getPlanetType(): PlanetTypeInterface
    {
        return $this->colony->getPlanetType();
    }

    public function getProductionSum(): int
    {
        return $this->colony->getProductionSum();
    }

    public function getCommodityUseView(): array
    {
        return $this->commodityConsumption->getConsumption($this->colony);
    }

    public function isDefended(): bool
    {
        return $this->colony->isDefended();
    }

    public function isBlocked(): bool
    {
        return $this->colony->isBlocked();
    }

    public function getStorageStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Lager'))
            ->setMaxValue($this->colony->getMaxStorage())
            ->setValue($this->colony->getStorageSum())
            ->render();
    }

    public function getEpsStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_YELLOW)
            ->setLabel(_('Energie'))
            ->setMaxValue($this->colony->getMaxEps())
            ->setValue($this->colony->getEps())
            ->render();
    }
}
