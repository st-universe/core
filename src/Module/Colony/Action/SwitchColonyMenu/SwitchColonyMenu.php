<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\SwitchColonyMenu;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlans;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademy;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyard;
use Stu\Module\Colony\View\ShowModuleFab\ShowModuleFab;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;
use Stu\Module\Colony\View\ShowWaste\ShowWaste;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class SwitchColonyMenu implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SWITCH_COLONYMENU';

    private ColonyLoaderInterface $colonyLoader;

    private BuildingFunctionRepositoryInterface $buildingFunctionRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );
        $menu = request::getIntFatal('menu');

        $colonySurface = $this->colonyLibFactory->createColonySurface($colony);

        switch ($menu) {
            case ColonyEnum::MENU_BUILD:
                $game->setView("SHOW_BUILDMENU");
                return;
            case ColonyEnum::MENU_OPTION:
                $game->setView("SHOW_MISC");
                return;
            case ColonyEnum::MENU_SOCIAL:
                $game->setView("SHOW_SOCIAL");
                return;
            case ColonyEnum::MENU_BUILDINGS:
                $game->setView("SHOW_BUILDING_MGMT");
                return;
            case ColonyEnum::MENU_SHIPYARD:
                if ($colonySurface->hasShipyard()) {
                    $game->setView(ShowShipyard::VIEW_IDENTIFIER);
                    $func = $this->buildingFunctionRepository->find((int) request::getIntFatal('func'));
                    $game->setTemplateVar('FUNC', $func);
                    return;
                }
            case ColonyEnum::MENU_BUILDPLANS:
                if ($colonySurface->hasShipyard()) {
                    $game->setView(ShowBuildPlans::VIEW_IDENTIFIER);
                    $func = $this->buildingFunctionRepository->find((int) request::getIntFatal('func'));
                    $game->setTemplateVar('FUNC', $func);
                    return;
                }
            case ColonyEnum::MENU_AIRFIELD:
                if ($colonySurface->hasAirfield()) {
                    $game->setView("SHOW_AIRFIELD");
                    return;
                }
            case ColonyEnum::MENU_MODULEFAB:
                if ($colonySurface->hasModuleFab()) {
                    $game->setView(ShowModuleFab::VIEW_IDENTIFIER);
                    return;
                }
            case ColonyEnum::MENU_FIGHTER_SHIPYARD:
                if ($this->hasSpecialBuilding($colony, BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD)) {
                    $game->setView(ShowFighterShipyard::VIEW_IDENTIFIER);
                    return;
                }
            case ColonyEnum::MENU_TORPEDOFAB:
                if ($this->hasSpecialBuilding($colony, BuildingEnum::BUILDING_FUNCTION_TORPEDO_FAB)) {
                    $game->setView("SHOW_TORPEDO_FAB");
                    return;
                }
            case ColonyEnum::MENU_ACADEMY:
                if ($this->hasSpecialBuilding($colony, BuildingEnum::BUILDING_FUNCTION_ACADEMY)) {
                    $game->setView(ShowAcademy::VIEW_IDENTIFIER);
                    return;
                }
            case ColonyEnum::MENU_WASTE:
                if ($this->hasSpecialBuilding($colony, BuildingEnum::BUILDING_FUNCTION_WAREHOUSE)) {
                    $game->setView(ShowWaste::VIEW_IDENTIFIER);
                    return;
                }
            case ColonyEnum::MENU_FAB_HALL:
                if ($this->hasSpecialBuilding($colony, BuildingEnum::BUILDING_FUNCTION_FABRICATION_HALL)) {
                    //$game->setView(ShowFabricationHall::VIEW_IDENTIFIER);
                    $game->setView(ShowModuleFab::VIEW_IDENTIFIER);
                    return;
                }
            case ColonyEnum::MENU_TECH_CENTER:
                if ($this->hasSpecialBuilding($colony, BuildingEnum::BUILDING_FUNCTION_TECH_CENTER)) {
                    //$game->setView(ShowTechCenter::VIEW_IDENTIFIER);
                    $game->setView(ShowModuleFab::VIEW_IDENTIFIER);
                    return;
                }
            case ColonyEnum::MENU_INFO:
            default:
                $game->setView("SHOW_MANAGEMENT");
                return;
        }
    }

    private function hasSpecialBuilding(ColonyInterface $colony, $function)
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony->getId(),
            [$function],
            [0, 1]
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
