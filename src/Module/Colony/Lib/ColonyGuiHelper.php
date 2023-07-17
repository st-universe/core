<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyGuiHelper implements ColonyGuiHelperInterface
{
    private CommodityRepositoryInterface $commodityRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->commodityRepository = $commodityRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function getColonyMenu(int $menuId): string
    {
        switch ($menuId) {
            case ColonyEnum::MENU_OPTION:
                return 'cm_misc';
            case ColonyEnum::MENU_BUILD:
                return 'cm_buildmenu';
            case ColonyEnum::MENU_SOCIAL:
                return 'cm_social';
            case ColonyEnum::MENU_BUILDINGS:
                return 'cm_building_mgmt';
            case ColonyEnum::MENU_AIRFIELD:
                return 'cm_airfield';
            case ColonyEnum::MENU_MODULEFAB:
                return 'cm_modulefab';
            default:
                return 'cm_management';
        }
    }

    public function register(ColonyInterface $colony, GameControllerInterface $game): void
    {
        $energyProduction = $this->planetFieldRepository->getEnergyProductionByColony($colony->getId());
        $width = 360;
        $bars = [];
        $epsBar = [];
        if ($energyProduction < 0) {
            $prod = abs($energyProduction);
            if ($colony->getEps() - $prod < 0) {
                $bars[StatusBarColorEnum::STATUSBAR_RED] = $colony->getEps();
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps();
            } else {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $colony->getEps() - $prod;
                $bars[StatusBarColorEnum::STATUSBAR_RED] = $prod;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps();
            }
        }
        if ($energyProduction > 0) {
            if ($colony->getEps() + $energyProduction > $colony->getMaxEps()) {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $colony->getEps();
                if ($colony->getEps() < $colony->getMaxEps()) {
                    $bars[StatusBarColorEnum::STATUSBAR_GREEN] = $colony->getMaxEps() - $colony->getEps();
                }
            } else {
                $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $colony->getEps();
                $bars[StatusBarColorEnum::STATUSBAR_GREEN] = $energyProduction;
                $bars[StatusBarColorEnum::STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps() - $energyProduction;
            }
        }
        if ($energyProduction == 0) {
            $bars[StatusBarColorEnum::STATUSBAR_YELLOW] = $colony->getEps();
            $bars[StatusBarColorEnum::STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps();
        }
        foreach ($bars as $color => $value) {
            if ($colony->getMaxEps() < $value) {
                $value = $colony->getMaxEps();
            }
            if ($value <= 0) {
                continue;
            }
            $epsBar[] = sprintf(
                '<img src="assets/bars/balken.png" style="background-color: #%s;height: 12px; width: %dpx;" title="%s" />',
                $color,
                round($width / 100 * (100 / $colony->getMaxEps() * $value)),
                'Energieproduktion'
            );
        }

        $commodities = $this->commodityRepository->getByType(CommodityTypeEnum::COMMODITY_TYPE_STANDARD);
        $stor = $colony->getStorage();
        $prod = $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction();

        $storage = [];
        foreach ($commodities as $value) {
            $commodityId = $value->getId();
            if (array_key_exists($commodityId, $prod)) {
                $storage[$commodityId]['commodity'] = $value;
                $storage[$commodityId]['production'] = $prod[$commodityId];
                if (!$stor->containsKey($commodityId)) {
                    $storage[$commodityId]['storage'] = false;
                } else {
                    $storage[$commodityId]['storage'] = $stor[$commodityId];
                }
            } elseif ($stor->containsKey($commodityId)) {
                $storage[$commodityId]['commodity'] = $value;
                $storage[$commodityId]['storage'] = $stor[$commodityId];
                $storage[$commodityId]['production'] = false;
            }
        }

        $depositMinings = $colony->getUserDepositMinings();

        $commodities = $this->commodityRepository->getByType(CommodityTypeEnum::COMMODITY_TYPE_EFFECT);
        $effects = [];
        foreach ($commodities as $value) {
            $commodityId = $value->getId();

            //skip deposit effects on asteroid
            if (array_key_exists($commodityId, $depositMinings)) {
                continue;
            }

            if (!array_key_exists($commodityId, $prod) || $prod[$commodityId]->getProduction() == 0) {
                continue;
            }
            $effects[$commodityId]['commodity'] = $value;
            $effects[$commodityId]['production'] = $prod[$commodityId];
        }


        $game->setTemplateVar(
            'EPS_STATUS_BAR',
            $epsBar
        );

        $shieldingManager = $this->colonyLibFactory->createColonyShieldingManager($colony);

        if ($shieldingManager->hasShielding()) {
            $game->setTemplateVar(
                'SHIELD_STATUS_BAR',
                $this->buildShieldBar($shieldingManager, $colony)
            );
        }
        $game->setTemplateVar('STORAGE', $storage);
        $game->setTemplateVar('EFFECTS', $effects);
        $game->setTemplateVar(
            'PRODUCTION_SUM',
            $this->colonyLibFactory->createColonyProductionSumReducer()->reduce($prod)
        );
    }

    private function buildShieldBar(
        ColonyShieldingManagerInterface $colonyShieldingManager,
        ColonyInterface $colony
    ): array {
        $shieldBar = [];
        $bars = [];
        $width = 360;

        $maxShields = $colonyShieldingManager->getMaxShielding();

        if ($colonyShieldingManager->isShieldingEnabled()) {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_ON] = $colony->getShields();
        } else {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_OFF] = $colony->getShields();
        }
        $bars[StatusBarColorEnum::STATUSBAR_GREY] = $maxShields - $colony->getShields();

        foreach ($bars as $color => $value) {
            if ($maxShields < $value) {
                $value = $maxShields;
            }
            if ($value <= 0) {
                continue;
            }
            $shieldBar[] = sprintf(
                '<img src="assets/bars/balken.png" style="background-color: #%s;height: 12px; width: %dpx;" title="%s" />',
                $color,
                round($width / 100 * (100 / $maxShields * $value)),
                'Schildstärke'
            );
        }

        return $shieldBar;
    }
}
