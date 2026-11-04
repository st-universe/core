<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\View\ShowAcademy\ShowAcademy;
use Stu\Module\Colony\View\ShowAirfield\ShowAirfield;
use Stu\Module\Colony\View\ShowBuildingManagement\ShowBuildingManagement;
use Stu\Module\Colony\View\ShowBuildMenu\ShowBuildMenu;
use Stu\Module\Colony\View\ShowBuildPlans\ShowBuildPlans;
use Stu\Module\Colony\View\ShowFighterShipyard\ShowFighterShipyard;
use Stu\Module\Colony\View\ShowMainscreen\ShowMainscreen;
use Stu\Module\Colony\View\ShowManagement\ShowManagement;
use Stu\Module\Colony\View\ShowMisc\ShowMisc;
use Stu\Module\Colony\View\ShowModuleFab\ShowModuleFab;
use Stu\Module\Colony\View\ShowShipDisassembly\ShowShipDisassembly;
use Stu\Module\Colony\View\ShowShipRepair\ShowShipRepair;
use Stu\Module\Colony\View\ShowShipRetrofit\ShowShipRetrofit;
use Stu\Module\Colony\View\ShowShipyard\ShowShipyard;
use Stu\Module\Colony\View\ShowSocial\ShowSocial;
use Stu\Module\Colony\View\ShowSubspaceTelescope\ShowSubspaceTelescope;
use Stu\Module\Colony\View\ShowTorpedoFab\ShowTorpedoFab;
use Stu\Module\Colony\View\ShowWaste\ShowWaste;
use Stu\Module\Colony\View\ShowSpecialResearch\ShowSpecialResearch;

enum ColonyMenuEnum: int
{
    case MENU_MAINSCREEN = 0;
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
    case MENU_SHIP_DISASSEMBLY = 17;
    case MENU_SHIP_REPAIR = 18;
    case MENU_SHIP_RETROFIT = 19;
    case MENU_SPECIAL_RESEARCH = 20;

    public static function getFor(mixed $value): ColonyMenuEnum
    {
        if ($value === null) {
            return self::MENU_MAINSCREEN;
        }

        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value) ?? self::MENU_MAINSCREEN;
    }

    public static function getMenuClass(ColonyMenuEnum $currentMenu, int $value): string
    {
        if ($currentMenu->value === $value) {
            return 'selected';
        }

        return "";
    }

    public function isBuildingFunctionMandatory(): bool
    {
        return match ($this) {
            self::MENU_SHIPYARD,
            self::MENU_SHIP_REPAIR,
            self::MENU_SHIP_DISASSEMBLY,
            self::MENU_BUILDPLANS,
            self::MENU_SHIP_RETROFIT => true,
            default => false
        };
    }

    /** @return array<BuildingFunctionEnum>|null */
    public function getNeededBuildingFunction(): ?array
    {
        return [
            self::MENU_AIRFIELD->value => [BuildingFunctionEnum::AIRFIELD],
            self::MENU_MODULEFAB->value => BuildingFunctionEnum::getModuleFabs(),
            self::MENU_SHIPYARD->value => BuildingFunctionEnum::getShipyards(),
            self::MENU_BUILDPLANS->value => BuildingFunctionEnum::getShipyards(),
            self::MENU_FIGHTER_SHIPYARD->value => [BuildingFunctionEnum::FIGHTER_SHIPYARD],
            self::MENU_TORPEDOFAB->value => [BuildingFunctionEnum::TORPEDO_FAB],
            self::MENU_ACADEMY->value => [BuildingFunctionEnum::ACADEMY],
            self::MENU_WASTE->value => [BuildingFunctionEnum::WAREHOUSE],
            self::MENU_FAB_HALL->value => [BuildingFunctionEnum::FABRICATION_HALL],
            self::MENU_TECH_CENTER->value => [BuildingFunctionEnum::TECH_CENTER],
            self::MENU_SUBSPACE_TELESCOPE->value => [BuildingFunctionEnum::SUBSPACE_TELESCOPE],
            self::MENU_SPECIAL_RESEARCH->value => [BuildingFunctionEnum::SPECIAL_RESEARCH],
            self::MENU_SHIP_DISASSEMBLY->value => BuildingFunctionEnum::getShipyards(),
            self::MENU_SHIP_REPAIR->value => BuildingFunctionEnum::getShipyards(),
            self::MENU_SHIP_RETROFIT->value => BuildingFunctionEnum::getShipyards(),
        ][$this->value] ?? null;
    }

    public function getViewIdentifier(): string
    {
        return [
            self::MENU_MAINSCREEN->value => ShowMainscreen::VIEW_IDENTIFIER,
            self::MENU_BUILD->value => ShowBuildMenu::VIEW_IDENTIFIER,
            self::MENU_INFO->value => ShowManagement::VIEW_IDENTIFIER,
            self::MENU_OPTION->value => ShowMisc::VIEW_IDENTIFIER,
            self::MENU_SOCIAL->value => ShowSocial::VIEW_IDENTIFIER,
            self::MENU_BUILDINGS->value => ShowBuildingManagement::VIEW_IDENTIFIER,
            self::MENU_AIRFIELD->value => ShowAirfield::VIEW_IDENTIFIER,
            self::MENU_MODULEFAB->value => ShowModuleFab::VIEW_IDENTIFIER,
            self::MENU_SHIPYARD->value => ShowShipyard::VIEW_IDENTIFIER,
            self::MENU_BUILDPLANS->value => ShowBuildPlans::VIEW_IDENTIFIER,
            self::MENU_FIGHTER_SHIPYARD->value => ShowFighterShipyard::VIEW_IDENTIFIER,
            self::MENU_TORPEDOFAB->value => ShowTorpedoFab::VIEW_IDENTIFIER,
            self::MENU_ACADEMY->value => ShowAcademy::VIEW_IDENTIFIER,
            self::MENU_WASTE->value => ShowWaste::VIEW_IDENTIFIER,
            self::MENU_FAB_HALL->value => ShowModuleFab::VIEW_IDENTIFIER,
            self::MENU_TECH_CENTER->value => ShowModuleFab::VIEW_IDENTIFIER,
            self::MENU_SUBSPACE_TELESCOPE->value => ShowSubspaceTelescope::VIEW_IDENTIFIER,
            self::MENU_SPECIAL_RESEARCH->value => ShowSpecialResearch::VIEW_IDENTIFIER,
            self::MENU_SHIP_DISASSEMBLY->value => ShowShipDisassembly::VIEW_IDENTIFIER,
            self::MENU_SHIP_REPAIR->value => ShowShipRepair::VIEW_IDENTIFIER,
            self::MENU_SHIP_RETROFIT->value => ShowShipRetrofit::VIEW_IDENTIFIER
        ][$this->value];
    }

    public function getTemplate(): string
    {
        return [
            self::MENU_MAINSCREEN->value => 'html/colony/menu/mainscreen.twig',
            self::MENU_BUILD->value => 'html/colony/menu/buildmenues.twig',
            self::MENU_INFO->value => 'html/colony/menu/management.twig',
            self::MENU_OPTION->value => 'html/colony/menu/miscellaneous.twig',
            self::MENU_SOCIAL->value => 'html/colony/menu/social.twig',
            self::MENU_BUILDINGS->value => 'html/colony/menu/buildingManagement.twig',
            self::MENU_AIRFIELD->value => 'html/colony/menu/airfield.twig',
            self::MENU_MODULEFAB->value => 'html/colony/menu/moduleFab.twig',
            self::MENU_SHIPYARD->value => 'html/colony/menu/shipyard.twig',
            self::MENU_BUILDPLANS->value => 'html/colony/menu/shipBuildplans.twig',
            self::MENU_FIGHTER_SHIPYARD->value => 'html/colony/menu/fighterShipyard.twig',
            self::MENU_TORPEDOFAB->value => 'html/colony/menu/torpedoFab.twig',
            self::MENU_ACADEMY->value => 'html/colony/menu/academy.twig',
            self::MENU_WASTE->value => 'html/colony/menu/waste.twig',
            self::MENU_FAB_HALL->value => 'html/colony/menu/componentFab.twig',
            self::MENU_TECH_CENTER->value => 'html/colony/menu/componentFab.twig',
            self::MENU_SUBSPACE_TELESCOPE->value => 'html/colony/menu/telescope.twig',
            self::MENU_SPECIAL_RESEARCH->value => 'html/colony/menu/specialResearch.twig',
            self::MENU_SHIP_DISASSEMBLY->value => 'html/colony/menu/shipDisassembly.twig',
            self::MENU_SHIP_REPAIR->value => 'html/colony/menu/shipRepair.twig',
            self::MENU_SHIP_RETROFIT->value => 'html/colony/menu/shipRetrofit.twig'
        ][$this->value];
    }

    /** @return array<ColonyComponentEnum> */
    public function getNecessaryGuiComponents(): array
    {
        return match ($this) {
            // mainscreen
            self::MENU_MAINSCREEN => [
                ColonyComponentEnum::SURFACE,
                ColonyComponentEnum::SHIELDING,
                ColonyComponentEnum::EPS_BAR,
                ColonyComponentEnum::STORAGE,
                ColonyComponentEnum::MANAGEMENT,
                ColonyComponentEnum::EFFECTS
            ],

            // submenues
            self::MENU_BUILD => [ColonyComponentEnum::BUILD_MENUES],
            self::MENU_INFO => [ColonyComponentEnum::MANAGEMENT, ColonyComponentEnum::EFFECTS],
            self::MENU_OPTION,
            self::MENU_WASTE,
            self::MENU_SUBSPACE_TELESCOPE => [],
            self::MENU_SOCIAL => [ColonyComponentEnum::SOCIAL],
            self::MENU_BUILDINGS => [ColonyComponentEnum::BUILDING_MANAGEMENT],

            // menues without surface
            self::MENU_AIRFIELD => [ColonyComponentEnum::AIRFIELD],
            self::MENU_MODULEFAB,
            self::MENU_FAB_HALL,
            self::MENU_TECH_CENTER,
            self::MENU_SHIPYARD => [ColonyComponentEnum::SHIPYARD],
            self::MENU_BUILDPLANS => [ColonyComponentEnum::SHIP_BUILDPLANS],
            self::MENU_FIGHTER_SHIPYARD => [ColonyComponentEnum::FIGHTER_SHIPYARD],
            self::MENU_TORPEDOFAB => [ColonyComponentEnum::TORPEDO_FAB],
            self::MENU_ACADEMY => [ColonyComponentEnum::ACADEMY],
            self::MENU_SPECIAL_RESEARCH => [ColonyComponentEnum::SPECIAL_RESEARCH],
            self::MENU_SHIP_DISASSEMBLY => [ColonyComponentEnum::SHIP_DISASSEMBLY],
            self::MENU_SHIP_REPAIR => [ColonyComponentEnum::SHIP_REPAIR],
            self::MENU_SHIP_RETROFIT => [ColonyComponentEnum::SHIP_RETROFIT]
        };
    }
}
