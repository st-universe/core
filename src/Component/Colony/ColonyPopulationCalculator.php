<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Override;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\ColonyInterface;

final class ColonyPopulationCalculator implements ColonyPopulationCalculatorInterface
{
    private ?int $positive_effect_secondary = null;

    private ?int $positive_effect_primary = null;

    /**
     * @param array<int, ColonyProduction> $production
     */
    public function __construct(private PlanetFieldHostInterface $host, private array $production) {}

    #[Override]
    public function getFreeAssignmentCount(): int
    {
        if (!$this->host instanceof ColonyInterface) {
            return 0;
        }

        return max(0, $this->getCrewLimit() - $this->host->getCrewAssignmentAmount());
    }

    #[Override]
    public function getCrewLimit(): int
    {
        return (int) floor(
            10 +
                min(
                    max(
                        ($this->getPositiveEffectPrimary() - (4 * max(
                            0,
                            $this->getNegativeEffect() - $this->getPositiveEffectSecondary()
                        ))),
                        0
                    ),
                    $this->host->getWorkers()
                ) / 5 * $this->getLifeStandardPercentage() / 100
        );
    }

    #[Override]
    public function getLifeStandardPercentage(): int
    {
        $colonyProduction = $this->production[CommodityTypeEnum::COMMODITY_EFFECT_LIFE_STANDARD] ?? null;
        $production = $colonyProduction !== null ? $colonyProduction->getProduction() : 0;

        if ($production == 0) {
            return 0;
        }

        if ($production > $this->host->getPopulation()) {
            return 100;
        }

        return (int)floor($production * 100 / $this->host->getPopulation());
    }

    #[Override]
    public function getNegativeEffect(): int
    {
        return (int) ceil($this->host->getPopulation() / 70);
    }

    #[Override]
    public function getPositiveEffectPrimary(): int
    {
        if ($this->positive_effect_primary === null) {
            $this->positive_effect_primary = 0;

            $commodity = $this->host->getUser()->getFaction()->getPrimaryEffectCommodity();

            if ($commodity !== null && array_key_exists($commodity->getId(), $this->production)) {
                $this->positive_effect_primary += $this->production[$commodity->getId()]->getProduction();
            }
        }

        return $this->positive_effect_primary;
    }

    #[Override]
    public function getPositiveEffectSecondary(): int
    {
        if ($this->positive_effect_secondary === null) {
            $this->positive_effect_secondary = 0;

            $commodity = $this->host->getUser()->getFaction()->getSecondaryEffectCommodity();

            if ($commodity !== null && array_key_exists($commodity->getId(), $this->production)) {
                $this->positive_effect_secondary += $this->production[$commodity->getId()]->getProduction();
            }
        }
        return $this->positive_effect_secondary;
    }

    #[Override]
    public function getGrowth(): int
    {
        $host = $this->host;
        if (!$host instanceof ColonyInterface) {
            return 0;
        }

        $changeable = $host->getChangeable();

        if ($changeable->getImmigrationState() === false) {
            return 0;
        }

        // TBD: depends on social things. return dummy for now
        $im = ceil((($changeable->getMaxBev() - $host->getPopulation()) / 3) / 100 * $host->getColonyClass()->getBevGrowthRate() *  $this->getLifeStandardPercentage() / 50);
        if ($host->getPopulation() + $im > $changeable->getMaxBev()) {
            $im = $changeable->getMaxBev() - $host->getPopulation();
        }
        if ($changeable->getPopulationLimit() > 0 && $host->getPopulation() + $im > $changeable->getPopulationLimit()) {
            $im = $changeable->getPopulationLimit() - $host->getPopulation();
        }
        if ($im < 0) {
            return 0;
        }
        return (int) $im;
    }
}
