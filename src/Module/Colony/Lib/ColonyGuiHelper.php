<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Tuple;

final class ColonyGuiHelper implements ColonyGuiHelperInterface
{
    private $commodityRepository;

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
            $epsBar[] = new Tuple($color,
                round($width / 100 * (100 / $colony->getMaxEps() * $value)));
        }


        $goods = $this->commodityRepository->getByType(CommodityTypeEnum::GOOD_TYPE_STANDARD);
        $stor = $colony->getStorage();
        $prod = $colony->getProduction();
        $storage = [];
        foreach ($goods as $value) {
            $commodityId = $value->getId();
            if (array_key_exists($commodityId, $prod)) {
                $storage[$commodityId]['good'] = $value;
                $storage[$commodityId]['production'] = $prod[$commodityId];
                if (!$stor->containsKey($commodityId)) {
                    $storage[$commodityId]['storage'] = false;
                } else {
                    $storage[$commodityId]['storage'] = $stor[$commodityId];
                }
            } elseif ($stor->containsKey($commodityId)) {
                $storage[$commodityId]['good'] = $value;
                $storage[$commodityId]['storage'] = $stor[$commodityId];
                $storage[$commodityId]['production'] = false;
            }
        }

        $goods = $this->commodityRepository->getByType(CommodityTypeEnum::GOOD_TYPE_EFFECT);
        $effets = [];
        foreach ($goods as $value) {
            $commodityId = $value->getId();
            if (!array_key_exists($commodityId, $prod) || $prod[$commodityId]->getProduction() == 0) {
                continue;
            }
            $effets[$commodityId]['good'] = $value;
            $effets[$commodityId]['production'] = $prod[$commodityId];
        }

        $game->setTemplateVar('EPS_BAR', $epsBar);
        $game->setTemplateVar('STORAGE', $storage);
        $game->setTemplateVar('EFFECTS', $effets);
    }
}
