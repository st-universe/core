<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Component;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Component\ComponentEnumInterface;

enum ColonyComponentEnum: string implements ComponentEnumInterface
{
    // mainscreen
    case SHIELDING = 'SHIELDING';
    case EPS_BAR = 'EPS_BAR';
    case SURFACE = 'SURFACE';
    case STORAGE = 'STORAGE';

        // submenues
    case MANAGEMENT = 'MANAGEMENT';
    case EFFECTS = 'EFFECTS';
    case BUILD_MENUES = 'BUILD_MENUES';
    case SOCIAL = 'SOCIAL';
    case BUILDING_MANAGEMENT = 'BUILDING_MANAGEMENT';

        // menues
    case ACADEMY = 'ACADEMY';
    case AIRFIELD = 'AIRFIELD';
    case MODULE_FAB = 'MODULE_FAB';
    case TORPEDO_FAB = 'TORPEDO_FAB';
    case SHIPYARD = 'SHIPYARD';
    case FIGHTER_SHIPYARD = 'FIGHTER_SHIPYARD';
    case SHIP_BUILDPLANS = 'SHIP_BUILDPLANS';
    case SHIP_REPAIR = 'SHIP_REPAIR';
    case SHIP_DISASSEMBLY = 'SHIP_DISASSEMBLY';
    case SHIP_RETROFIT = 'SHIP_RETROFIT';

    #[Override]
    public function getModuleView(): ModuleEnum
    {
        return ModuleEnum::COLONY;
    }

    #[Override]
    public function getTemplate(): string
    {
        return match ($this) {
            self::SHIELDING => 'html/colony/component/colonyShields.twig',
            self::EPS_BAR => 'html/colony/component/colonyEps.twig',
            self::SURFACE => 'html/colony/component/colonySurface.twig',
            self::STORAGE => 'html/colony/component/colonyStorage.twig',
            self::MANAGEMENT => '',
            self::EFFECTS => '',
            self::BUILD_MENUES => '',
            self::SOCIAL => '',
            self::BUILDING_MANAGEMENT => '',
            self::ACADEMY => '',
            self::AIRFIELD => '',
            self::MODULE_FAB => '',
            self::TORPEDO_FAB => '',
            self::SHIPYARD => '',
            self::FIGHTER_SHIPYARD => '',
            self::SHIP_BUILDPLANS => '',
            self::SHIP_REPAIR => '',
            self::SHIP_DISASSEMBLY => '',
            self::SHIP_RETROFIT => ''
        };
    }

    #[Override]
    public function getRefreshIntervalInSeconds(): ?int
    {
        return null;
    }

    #[Override]
    public function hasTemplateVariables(): bool
    {
        return true;
    }

    #[Override]
    public function getValue(): string
    {
        return $this->name;
    }
}
