<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\SwitchColonyMenu;

use BuildingFunctions;
use Colfields;
use Colony;
use ModuleBuildingFunction;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademy;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyard;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;

final class SwitchColonyMenu implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SWITCH_COLONYMENU';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );
        $menu = request::getIntFatal('menu');
        switch ($menu) {
            case MENU_BUILD:
                $game->setView("SHOW_BUILDMENU");
                return;
            case MENU_OPTION:
                $game->setView("SHOW_MISC");
                return;
            case MENU_SOCIAL:
                $game->setView("SHOW_SOCIAL");
                return;
            case MENU_BUILDINGS:
                $game->setView("SHOW_BUILDING_MGMT");
                return;
            case MENU_SHIPYARD:
                if ($colony->hasShipyard()) {
                    $game->setView(ShowShipyard::VIEW_IDENTIFIER);
                    $func = new BuildingFunctions(request::getIntFatal('func'));
                    $game->setTemplateVar('FUNC', $func);
                    return;
                }
            case MENU_BUILDPLANS:
                if ($colony->hasShipyard()) {
                    $game->setView("SHOW_BUILDPLANS");
                    $func = new BuildingFunctions(request::getIntFatal('func'));
                    $game->setTemplateVar('FUNC', $func);
                    return;
                }
            case MENU_AIRFIELD:
                if ($colony->hasAirfield()) {
                    $game->setView("SHOW_AIRFIELD");
                    return;
                }
            case MENU_MODULEFAB:
                if ($colony->hasModuleFab()) {
                    $game->setView('SHOW_MODULEFAB');
                    $func = new BuildingFunctions(request::getIntFatal('func'));
                    $game->setTemplateVar('FUNC', $func);
                    $game->setTemplateVar(
                        'MODULE_LIST',
                        ModuleBuildingFunction::getByFunctionAndUser($func->getFunction(), $userId)
                    );
                    return;
                }
            case MENU_FIGHTER_SHIPYARD:
                if ($this->hasSpecialBuilding($colony, BUILDING_FUNCTION_FIGHTER_SHIPYARD)) {
                    $game->setView(ShowFighterShipyard::VIEW_IDENTIFIER);
                    return;
                }
            case MENU_TORPEDOFAB:
                if ($this->hasSpecialBuilding($colony, BUILDING_FUNCTION_TORPEDO_FAB)) {
                    $game->setView("SHOW_TORPEDO_FAB");
                    return;
                }
            case MENU_ACADEMY:
                if ($this->hasSpecialBuilding($colony, BUILDING_FUNCTION_ACADEMY)) {
                    $game->setView(ShowAcademy::VIEW_IDENTIFIER);
                    return;
                }
            case MENU_INFO:
            default:
                $game->setView("SHOW_MANAGEMENT");
                return;
        }
    }

    private function hasSpecialBuilding(Colony $colony, $function)
    {
        return count(Colfields::getFieldsByBuildingFunction($colony->getId(), $function)) > 0;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
