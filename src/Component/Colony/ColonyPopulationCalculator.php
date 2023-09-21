<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use RuntimeException;
use Stu\Component\Faction\FactionEnum;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\ColonyInterface;

final class ColonyPopulationCalculator implements ColonyPopulationCalculatorInterface
{
    private ColonyInterface $colony;

    private ?int $positive_effect_secondary = null;

    private ?int $positive_effect_primary = null;

    /** @var array<int, ColonyProduction> */
    private array $production;

    /**
     * @param array<int, ColonyProduction> $production
     */
    public function __construct(
        ColonyInterface $colony,
        array $production
    ) {
        $this->colony = $colony;
        $this->production = $production;
    }

    public function getFreeAssignmentCount(): int
    {
        return max(0, $this->getCrewLimit() - $this->colony->getCrewAssignmentAmount());
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
                    $this->colony->getWorkers()
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

        if ($production > $this->colony->getPopulation()) {
            return 100;
        }

        return (int)floor($production * 100 / $this->colony->getPopulation());
    }

    public function getNegativeEffect(): int
    {
        return (int) ceil($this->colony->getPopulation() / 70);
    }

    public function getPositiveEffectPrimary(): int
    {
        if ($this->positive_effect_primary === null) {
            // TODO we should use a faction-factory...
            switch ($this->colony->getUser()->getFactionId()) {
                case FactionEnum::FACTION_FEDERATION:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_FED_PRIMARY;
                    break;
                case FactionEnum::FACTION_ROMULAN:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_ROMULAN_PRIMARY;
                    break;
                case FactionEnum::FACTION_KLINGON:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_KLINGON_PRIMARY;
                    break;
                case FactionEnum::FACTION_CARDASSIAN:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_CARDASSIAN_PRIMARY;
                    break;
                case FactionEnum::FACTION_FERENGI:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_FERENGI_PRIMARY;
                    break;
                default:
                    throw new RuntimeException('faction id is not configured');
            }
            $this->positive_effect_primary = 0;
            if (!array_key_exists($key, $this->production)) {
                return 0;
            }
            $this->positive_effect_primary += $this->production[$key]->getProduction();
        }
        return $this->positive_effect_primary;
    }

    public function getPositiveEffectSecondary(): int
    {
        if ($this->positive_effect_secondary === null) {
            $this->positive_effect_secondary = 0;
            // XXX we should use a faction-factory...
            switch ($this->colony->getUser()->getFactionId()) {
                case FactionEnum::FACTION_FEDERATION:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_FED_SECONDARY;
                    break;
                case FactionEnum::FACTION_ROMULAN:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_ROMULAN_SECONDARY;
                    break;
                case FactionEnum::FACTION_KLINGON:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_KLINGON_SECONDARY;
                    break;
                case FactionEnum::FACTION_CARDASSIAN:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_CARDASSIAN_SECONDARY;
                    break;
                case FactionEnum::FACTION_FERENGI:
                    $key = ColonyEnum::COMMODITY_SATISFACTION_FERENGI_SECONDARY;
                    break;
                default:
                    throw new RuntimeException('faction id is not configured');
            }
            if (!array_key_exists($key, $this->production)) {
                return 0;
            }
            $this->positive_effect_secondary += $this->production[$key]->getProduction();
        }
        return $this->positive_effect_secondary;
    }

    public function getGrowth(): int
    {
        if ($this->colony->getImmigrationState() === false) {
            return 0;
        }
        // TBD: depends on social things. return dummy for now
        $im = ceil(($this->colony->getMaxBev() - $this->colony->getPopulation()) / 3);
        if ($this->colony->getPopulation() + $im > $this->colony->getMaxBev()) {
            $im = $this->colony->getMaxBev() - $this->colony->getPopulation();
        }
        if ($this->colony->getPopulationLimit() > 0 && $this->colony->getPopulation() + $im > $this->colony->getPopulationLimit()) {
            $im = $this->colony->getPopulationLimit() - $this->colony->getPopulation();
        }
        if ($im < 0) {
            return 0;
        }
        return (int) round(
            $im / 100 * $this->colony->getColonyClass()->getBevGrowthRate() *  $this->getLifeStandardPercentage() / 50
        );
    }

    public function getPositiveEffectPrimaryDescription(): string
    {
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Zufriedenheit');
            case FactionEnum::FACTION_ROMULAN:
                return _('Loyalität');
            case FactionEnum::FACTION_KLINGON:
                return _('Ehre');
            case FactionEnum::FACTION_CARDASSIAN:
                return _('Stolz');
            case FactionEnum::FACTION_FERENGI:
                return _('Wohlstand');
        }
        return '';
    }

    public function getPositiveEffectSecondaryDescription(): string
    {
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Bildung');
            case FactionEnum::FACTION_ROMULAN:
                return _('Imperiales Gedankengut');
            case FactionEnum::FACTION_KLINGON:
                return _('Kampftraining');
            case FactionEnum::FACTION_CARDASSIAN:
                return _('Patriotismus');
            case FactionEnum::FACTION_FERENGI:
                return _('Profitgier');
        }
        return '';
    }

    public function getNegativeEffectDescription(): string
    {
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
            case FactionEnum::FACTION_ROMULAN:
            case FactionEnum::FACTION_KLINGON:
            case FactionEnum::FACTION_CARDASSIAN:
            case FactionEnum::FACTION_FERENGI:
                return _('Bevölkerungsdichte');
        }
        return '';
    }
}
