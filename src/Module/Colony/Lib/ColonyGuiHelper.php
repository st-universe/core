<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ColonyGuiHelper implements ColonyGuiHelperInterface
{
    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->commodityRepository = $commodityRepository;
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

    public function register(ColonyInterface $colony, GameControllerInterface $game)
    {
        $energyProduction = $colony->getEpsProduction();
        $width = 360;
        $bars = array();
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
                _('Energieproduktion')
            );
        }

        $commodities = $this->commodityRepository->getByType(CommodityTypeEnum::COMMODITY_TYPE_STANDARD);
        $stor = $colony->getStorage();
        $prod = $colony->getProduction();
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
        if ($colony->hasShields()) {
            $game->setTemplateVar(
                'SHIELD_STATUS_BAR',
                $this->buildShieldBar($colony)
            );
        }
        $game->setTemplateVar('STORAGE', $storage);
        $game->setTemplateVar('EFFECTS', $effects);
    }

    private function buildShieldBar(ColonyInterface $colony): array
    {
        $shieldBar = [];
        $bars = array();
        $width = 360;

        if ($colony->getShieldState()) {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_ON] = $colony->getShields();
        } else {
            $bars[StatusBarColorEnum::STATUSBAR_SHIELD_OFF] = $colony->getShields();
        }
        $bars[StatusBarColorEnum::STATUSBAR_GREY] = $colony->getMaxShields() - $colony->getShields();

        foreach ($bars as $color => $value) {
            if ($colony->getMaxShields() < $value) {
                $value = $colony->getMaxShields();
            }
            if ($value <= 0) {
                continue;
            }
            $shieldBar[] = sprintf(
                '<img src="assets/bars/balken.png" style="background-color: #%s;height: 12px; width: %dpx;" title="%s" />',
                $color,
                round($width / 100 * (100 / $colony->getMaxShields() * $value)),
                _('Schildstärke')
            );
        }

        return $shieldBar;
    }
}
