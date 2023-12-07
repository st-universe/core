<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\ColonyInterface;

final class ColonyPopulationCalculator implements ColonyPopulationCalculatorInterface
{
    private PlanetFieldHostInterface $host;

    private ?int $positive_effect_secondary = null;

    private ?int $positive_effect_primary = null;

    /** @var array<int, ColonyProduction> */
    private array $production;

    /**
     * @param array<int, ColonyProduction> $production
     */
    public function __construct(
        PlanetFieldHostInterface $host,
        array $production
    ) {
        $this->host = $host;
        $this->production = $production;
    }

    public function getFreeAssignmentCount(): int
    {
        if (!$this->host instanceof ColonyInterface) {
            return 0;
        }

        return max(0, $this->getCrewLimit() - $this->host->getCrewAssignmentAmount());
    }

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

    public function getNegativeEffect(): int
    {
        return (int) ceil($this->host->getPopulation() / 70);
    }

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

    public function getGrowth(): int
    {
        $host = $this->host;
        if (!$host instanceof ColonyInterface) {
            return 0;
        }

        if ($host->getImmigrationState() === false) {
            return 0;
        }

        // TBD: depends on social things. return dummy for now
        $im = ceil((($host->getMaxBev() - $host->getPopulation()) / 3) / 100 * $host->getColonyClass()->getBevGrowthRate() *  $this->getLifeStandardPercentage() / 50);
        if ($host->getPopulation() + $im > $host->getMaxBev()) {
            $im = $host->getMaxBev() - $host->getPopulation();
        }
        if ($host->getPopulationLimit() > 0 && $host->getPopulation() + $im > $host->getPopulationLimit()) {
            $im = $host->getPopulationLimit() - $host->getPopulation();
        }
        if ($im < 0) {
            return 0;
        }
        return (int) $im;
    }
}
