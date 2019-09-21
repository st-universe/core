<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\SwitchColonyMenu;

use request;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlans;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademy;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyard;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class SwitchColonyMenu implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SWITCH_COLONYMENU';

    private $colonyLoader;

    private $buildingFunctionRepository;

    private $planetFieldRepository;

    private $colonyLibFactory;

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
                if ($colonySurface->hasShipyard()) {
                    $game->setView(ShowShipyard::VIEW_IDENTIFIER);
                    $func = $this->buildingFunctionRepository->find((int) request::getIntFatal('func'));
                    $game->setTemplateVar('FUNC', $func);
                    return;
                }
            case MENU_BUILDPLANS:
                if ($colonySurface->hasShipyard()) {
                    $game->setView(ShowBuildPlans::VIEW_IDENTIFIER);
                    $func = $this->buildingFunctionRepository->find((int) request::getIntFatal('func'));
                    $game->setTemplateVar('FUNC', $func);
                    return;
                }
            case MENU_AIRFIELD:
                if ($colonySurface->hasAirfield()) {
                    $game->setView("SHOW_AIRFIELD");
                    return;
                }
            case MENU_MODULEFAB:
                if ($colonySurface->hasModuleFab()) {
                    $game->setView('SHOW_MODULEFAB');
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

    private function hasSpecialBuilding(ColonyInterface $colony, $function)
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
                $colony->getId(),
                [$function],
                [0,1]
            );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
