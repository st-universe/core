<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyListItem implements ColonyListItemInterface
{
    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyInterface $colony;

    private int $signatureCount;

    private CommodityConsumptionInterface $commodityConsumption;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    /** @var array<int, ColonyProduction>|null */
    private ?array $production = null;

    private ?ColonyPopulationCalculatorInterface $colonyPopulationCalculator = null;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        CommodityConsumptionInterface $commodityConsumption,
        ColonyInterface $colony,
        int $signatureCount
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
        $this->commodityConsumption = $commodityConsumption;
        $this->colony = $colony;
        $this->signatureCount = $signatureCount;
        $this->planetFieldRepository = $planetFieldRepository;
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
        return $this->getPopulationCalculator()->getGrowth();
    }

    public function getEps(): int
    {
        return $this->colony->getEps();
    }

    public function getMaxEps(): int
    {
        return $this->colony->getMaxEps();
    }

    public function getEnergyProduction(): int
    {
        return $this->planetFieldRepository->getEnergyProductionByHost($this->colony);
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

    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colony->getColonyClass();
    }

    public function getProductionSum(): int
    {
        return $this->colonyLibFactory->createColonyProductionSumReducer()->reduce(
            $this->getProduction()
        );
    }

    public function getCommodityUseView(): array
    {
        return $this->commodityConsumption->getConsumption(
            $this->getProduction(),
            $this->colony
        );
    }

    public function isDefended(): bool
    {
        return $this->colony->isDefended();
    }

    public function isBlocked(): bool
    {
        return $this->colony->isBlocked();
    }

    public function getCrewAssignmentAmount(): int
    {
        return $this->colony->getCrewAssignmentAmount();
    }

    public function getCrewTrainingAmount(): int
    {
        return $this->colony->getCrewTrainingAmount();
    }

    public function getCrewLimit(): int
    {
        return $this->getPopulationCalculator()->getCrewLimit();
    }

    public function getCrewLimitStyle(): string
    {
        $lifeStandardPercentage = $this->getPopulationCalculator()->getLifeStandardPercentage();

        if ($lifeStandardPercentage === 100) {
            return "color: green;";
        }

        if ($lifeStandardPercentage > 75) {
            return "color: yellow;";
        }

        if ($lifeStandardPercentage > 50) {
            return "color: orange;";
        }

        return "color: red;";
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

    /**
     * @return array<int, ColonyProduction>
     */
    private function getProduction(): array
    {
        if ($this->production === null) {
            $this->production = $this->colonyLibFactory->createColonyCommodityProduction($this->colony)->getProduction();
        }

        return $this->production;
    }

    private function getPopulationCalculator(): ColonyPopulationCalculatorInterface
    {
        if ($this->colonyPopulationCalculator === null) {
            $this->colonyPopulationCalculator = $this->colonyLibFactory->createColonyPopulationCalculator(
                $this->colony,
                $this->getProduction()
            );
        }

        return $this->colonyPopulationCalculator;
    }
}
