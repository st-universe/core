<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Component\Building\BuildingEnum;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademy;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfield;
use Stu\Module\Colony\View\ShowBuildingManagement\ShowBuildingManagement;
use Stu\Module\Colony\View\ShowBuildMenu\ShowBuildMenu;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlans;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyard;
use Stu\Module\Colony\View\ShowManagement\ShowManagement;
use Stu\Module\Colony\View\ShowMisc\ShowMisc;
use Stu\Module\Colony\View\ShowModuleFab\ShowModuleFab;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;
use Stu\Module\Colony\View\ShowSocial\ShowSocial;
use Stu\Module\Colony\View\ShowSubspaceTelescope\ShowSubspaceTelescope;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFab;
use Stu\Module\Colony\View\ShowWaste\ShowWaste;

enum ColonyMenuEnum: int
{
    case MENU_BUILD = 1;
    case MENU_INFO = 2;
    case MENU_OPTION = 3;
    case MENU_SOCIAL = 4;
    case MENU_BUILDINGS = 5;
    case MENU_AIRFIELD = 6;
    case MENU_MODULEFAB = 7;
    case MENU_SHIPYARD = 8;
    case MENU_BUILDPLANS = 9;
    case MENU_FIGHTER_SHIPYARD = 10;
    case MENU_TORPEDOFAB = 11;
    case MENU_ACADEMY = 12;
    case MENU_WASTE = 13;
    case MENU_FAB_HALL = 14;
    case MENU_TECH_CENTER = 15;
    case MENU_SUBSPACE_TELESCOPE = 16;

    public static function getFor(mixed $value): ColonyMenuEnum
    {
        if ($value === null) {
            return ColonyMenuEnum::MENU_INFO;
        }

        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value) ?? ColonyMenuEnum::MENU_INFO;
    }

    public static function getMenuClass(ColonyMenuEnum $currentMenu, int $value): string
    {
        if ($currentMenu->value === $value) {
            return 'selected';
        }

        return "";
    }


    /** @return array<int>|null */
    public function getNeededBuildingFunction(): ?array
    {
        return match ($this) {
            self::MENU_BUILD => null,
            self::MENU_INFO => null,
            self::MENU_OPTION => null,
            self::MENU_SOCIAL => null,
            self::MENU_BUILDINGS => null,
            self::MENU_AIRFIELD => [BuildingEnum::BUILDING_FUNCTION_AIRFIELD],
            self::MENU_MODULEFAB => BuildingFunctionTypeEnum::getModuleFabOptions(),
            self::MENU_SHIPYARD => BuildingFunctionTypeEnum::getShipyardOptions(),
            self::MENU_BUILDPLANS => BuildingFunctionTypeEnum::getShipyardOptions(),
            self::MENU_FIGHTER_SHIPYARD => [BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD],
            self::MENU_TORPEDOFAB => [BuildingEnum::BUILDING_FUNCTION_TORPEDO_FAB],
            self::MENU_ACADEMY => [BuildingEnum::BUILDING_FUNCTION_ACADEMY],
            self::MENU_WASTE => [BuildingEnum::BUILDING_FUNCTION_WAREHOUSE],
            self::MENU_FAB_HALL => [BuildingEnum::BUILDING_FUNCTION_FABRICATION_HALL],
            self::MENU_TECH_CENTER => [BuildingEnum::BUILDING_FUNCTION_TECH_CENTER],
            self::MENU_SUBSPACE_TELESCOPE => [BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE]
        };
    }

    public function getViewIdentifier(): string
    {
        return match ($this) {
            self::MENU_BUILD => ShowBuildMenu::VIEW_IDENTIFIER,
            self::MENU_INFO => ShowManagement::VIEW_IDENTIFIER,
            self::MENU_OPTION => ShowMisc::VIEW_IDENTIFIER,
            self::MENU_SOCIAL => ShowSocial::VIEW_IDENTIFIER,
            self::MENU_BUILDINGS => ShowBuildingManagement::VIEW_IDENTIFIER,
            self::MENU_AIRFIELD => ShowAirfield::VIEW_IDENTIFIER,
            self::MENU_MODULEFAB => ShowModuleFab::VIEW_IDENTIFIER,
            self::MENU_SHIPYARD => ShowShipyard::VIEW_IDENTIFIER,
            self::MENU_BUILDPLANS => ShowBuildPlans::VIEW_IDENTIFIER,
            self::MENU_FIGHTER_SHIPYARD => ShowFighterShipyard::VIEW_IDENTIFIER,
            self::MENU_TORPEDOFAB => ShowTorpedoFab::VIEW_IDENTIFIER,
            self::MENU_ACADEMY => ShowAcademy::VIEW_IDENTIFIER,
            self::MENU_WASTE => ShowWaste::VIEW_IDENTIFIER,
            self::MENU_FAB_HALL => ShowModuleFab::VIEW_IDENTIFIER,
            self::MENU_TECH_CENTER => ShowModuleFab::VIEW_IDENTIFIER,
            self::MENU_SUBSPACE_TELESCOPE => ShowSubspaceTelescope::VIEW_IDENTIFIER
        };
    }

    public function getTemplate(): string
    {
        return match ($this) {
            self::MENU_BUILD => 'html/colony/menu/buildmenues.twig',
            self::MENU_INFO => 'html/colony/menu/management.twig',
            self::MENU_OPTION => 'html/colony/menu/miscellaneous.twig',
            self::MENU_SOCIAL => 'html/colony/menu/social.twig',
            self::MENU_BUILDINGS => 'html/colony/menu/buildingManagement.twig',
            self::MENU_AIRFIELD => 'html/colony/menu/airfield.twig',
            self::MENU_MODULEFAB => 'html/colony/menu/moduleFab.twig',
            self::MENU_SHIPYARD => 'html/colony/menu/shipyard.twig',
            self::MENU_BUILDPLANS => 'html/colony/menu/shipBuildplans.twig',
            self::MENU_FIGHTER_SHIPYARD => 'html/colony/menu/fighterShipyard.twig',
            self::MENU_TORPEDOFAB => 'html/colony/menu/torpedoFab.twig',
            self::MENU_ACADEMY => 'html/colony/menu/academy.twig',
            self::MENU_WASTE => 'html/colony/menu/waste.twig',
            self::MENU_FAB_HALL => 'html/colony/menu/moduleFab.twig',
            self::MENU_TECH_CENTER => 'html/colony/menu/moduleFab.twig',
            self::MENU_SUBSPACE_TELESCOPE => 'html/colony/menu/telescope.twig'
        };
    }
}
