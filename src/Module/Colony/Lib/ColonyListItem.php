<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Colony\Lib\Gui\Component\EpsBarProvider;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyListItem implements ColonyListItemInterface
{
    /** @var array<int, ColonyProduction>|null */
    private ?array $production = null;

    private ?ColonyPopulationCalculatorInterface $colonyPopulationCalculator = null;

    private ?int $energyProduction = null;

    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private CommodityConsumptionInterface $commodityConsumption,
        private StatusBarFactoryInterface $statusBarFactory,
        private ColonyInterface $colony,
        private int $signatureCount
    ) {}

    #[Override]
    public function getId(): int
    {
        return $this->colony->getId();
    }

    #[Override]
    public function getName(): string
    {
        return $this->colony->getName();
    }

    #[Override]
    public function getSystem(): StarSystemInterface
    {
        return $this->colony->getSystem();
    }

    #[Override]
    public function getSX(): int
    {
        return $this->colony->getSX();
    }

    #[Override]
    public function getSY(): int
    {
        return $this->colony->getSY();
    }

    #[Override]
    public function getSignatureCount(): int
    {
        return $this->signatureCount;
    }

    #[Override]
    public function getPopulation(): int
    {
        return $this->colony->getPopulation();
    }

    #[Override]
    public function getHousing(): int
    {
        return $this->colony->getChangeable()->getMaxBev();
    }

    #[Override]
    public function getImmigration(): int
    {
        return $this->getPopulationCalculator()->getGrowth();
    }

    #[Override]
    public function getEps(): int
    {
        return $this->colony->getChangeable()->getEps();
    }

    #[Override]
    public function getMaxEps(): int
    {
        return $this->colony->getMaxEps();
    }

    #[Override]
    public function getEnergyProduction(): int
    {
        if ($this->energyProduction === null) {
            $this->energyProduction = $this->planetFieldRepository->getEnergyProductionByHost($this->colony);
        }

        return $this->energyProduction;
    }

    #[Override]
    public function getStorageSum(): int
    {
        return $this->colony->getStorageSum();
    }

    #[Override]
    public function getMaxStorage(): int
    {
        return $this->colony->getMaxStorage();
    }

    #[Override]
    public function getStorage(): Collection
    {
        return $this->colony->getStorage();
    }

    #[Override]
    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colony->getColonyClass();
    }

    #[Override]
    public function getProductionSum(): int
    {
        return $this->colonyLibFactory->createColonyProductionSumReducer()->reduce(
            $this->getProduction()
        );
    }

    #[Override]
    public function getCommodityUseView(): array
    {
        return $this->commodityConsumption->getConsumption(
            $this->getProduction(),
            $this->colony
        );
    }

    #[Override]
    public function isDefended(): bool
    {
        return $this->colony->isDefended();
    }

    #[Override]
    public function isBlocked(): bool
    {
        return $this->colony->isBlocked();
    }

    #[Override]
    public function getCrewAssignmentAmount(): int
    {
        return $this->colony->getCrewAssignmentAmount();
    }

    #[Override]
    public function getCrewTrainingAmount(): int
    {
        return $this->colony->getCrewTrainingAmount();
    }

    #[Override]
    public function getCrewLimit(): int
    {
        return $this->getPopulationCalculator()->getCrewLimit();
    }

    #[Override]
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

    #[Override]
    public function getStorageStatusBar(): string
    {
        return $this->statusBarFactory
            ->createStatusBar()
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Lager'))
            ->setMaxValue($this->colony->getMaxStorage())
            ->setValue($this->colony->getStorageSum())
            ->render();
    }

    #[Override]
    public function getEpsStatusBar(): string
    {
        return EpsBarProvider::getEpsStatusBar($this->colony, $this->getEnergyProduction(), 50);
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
