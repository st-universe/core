<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Good;
use Stu\Control\GameControllerInterface;
use Tuple;

final class ColonyGuiHelper implements ColonyGuiHelperInterface
{
    public function getColonyMenu(int $menuId): string
    {
        switch ($menuId) {
            case MENU_OPTION:
                return 'cm_misc';
            case MENU_BUILD:
                return 'cm_buildmenu';
            case MENU_SOCIAL:
                return 'cm_social';
            case MENU_BUILDINGS:
                return 'cm_building_mgmt';
            case MENU_AIRFIELD:
                return 'cm_airfield';
            case MENU_MODULEFAB:
                return 'cm_modulefab';
            default:
                return 'cm_management';
        }
    }

    public function register(\Colony $colony, GameControllerInterface $game)
    {
        $width = 360;
        $bars = array();
        $epsBar = [];
        if ($colony->getEpsProduction() < 0) {
            $prod = abs($colony->getEpsProduction());
            if ($colony->getEps() - $prod < 0) {
                $bars[STATUSBAR_RED] = $colony->getEps();
                $bars[STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps();
            } else {
                $bars[STATUSBAR_YELLOW] = $colony->getEps() - $prod;
                $bars[STATUSBAR_RED] = $prod;
                $bars[STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps();
            }
        }
        if ($colony->getEpsProduction() > 0) {
            $prod = $colony->getEpsProduction();
            if ($colony->getEps() + $prod > $colony->getMaxEps()) {
                $bars[STATUSBAR_YELLOW] = $colony->getEps();
                if ($colony->getEps() < $colony->getMaxEps()) {
                    $bars[STATUSBAR_GREEN] = $colony->getMaxEps() - $colony->getEps();
                }
            } else {
                $bars[STATUSBAR_YELLOW] = $colony->getEps();
                $bars[STATUSBAR_GREEN] = $prod;
                $bars[STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps() - $prod;
            }
        }
        if ($colony->getEpsProduction() == 0) {
            $bars[STATUSBAR_YELLOW] = $colony->getEps();
            $bars[STATUSBAR_GREY] = $colony->getMaxEps() - $colony->getEps();
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


        $goods = Good::getList('type=' . GOOD_TYPE_STANDARD);
        $stor = $colony->getStorage();
        $prod = $colony->getProduction();
        $storage = [];
        foreach ($goods as $key => $value) {
            if (array_key_exists($key, $prod)) {
                $storage[$key]['good'] = $value;
                $storage[$key]['production'] = $prod[$key];
                if (!$stor->offsetExists($key)) {
                    $storage[$key]['storage'] = false;
                } else {
                    $storage[$key]['storage'] = $stor->offsetGet($key);
                }
            } elseif ($stor->offsetExists($key)) {
                $storage[$key]['good'] = $value;
                $storage[$key]['storage'] = $stor->offsetGet($key);
                $storage[$key]['production'] = false;
            }
        }

        $goods = Good::getList('type=' . GOOD_TYPE_EFFECT);
        $prod = $colony->getProduction();
        $effets = [];
        foreach ($goods as $key => $value) {
            if (!array_key_exists($key, $prod) || $prod[$key]->getProduction() == 0) {
                continue;
            }
            $effets[$key]['good'] = $value;
            $effets[$key]['production'] = $prod[$key];
        }

        $game->setTemplateVar('EPS_BAR', $epsBar);
        $game->setTemplateVar('STORAGE', $storage);
        $game->setTemplateVar('EFFECTS', $effets);
    }
}