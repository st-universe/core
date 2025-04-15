<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Component\ComponentEnumInterface;

enum GameComponentEnum: string implements ComponentEnumInterface
{
    case COLONIES = 'COLONIES_NAVLET';
    case NAVIGATION = 'NAVIGATION';
    case NAGUS = 'NAGUS_POPUP';
    case PM = 'PM_NAVLET';
    case RESEARCH = 'RESEARCH_NAVLET';
    case SERVERTIME_AND_VERSION = 'SERVERTIME';
    case USER = 'USER_PROFILE';
    case OUTDATED = 'OUTDATED';

    #[Override]
    public function getModuleView(): ModuleEnum
    {
        return ModuleEnum::GAME;
    }

    #[Override]
    public function getTemplate(): string
    {
        return match ($this) {
            self::COLONIES => 'html/game/component/coloniesComponent.twig',
            self::NAVIGATION => 'html/game/component/navigationComponent.twig',
            self::NAGUS => 'html/game/component/nagusComponent.twig',
            self::PM => 'html/game/component/pmComponent.twig',
            self::RESEARCH => 'html/game/component/researchComponent.twig',
            self::SERVERTIME_AND_VERSION => 'html/game/component/serverTimeAndVersionComponent.twig',
            self::USER => 'html/game/component/userComponent.twig',
            self::OUTDATED => 'html/game/component/outdatedComponent.twig'
        };
    }

    #[Override]
    public function getRefreshIntervalInSeconds(): ?int
    {
        return match ($this) {
            self::PM => 60,
            self::SERVERTIME_AND_VERSION => 300,
            default => null
        };
    }

    #[Override]
    public function hasTemplateVariables(): bool
    {
        return match ($this) {
            self::NAVIGATION,
            self::OUTDATED => false,
            default => true
        };
    }

    #[Override]
    public function getValue(): string
    {
        return $this->value;
    }
}
