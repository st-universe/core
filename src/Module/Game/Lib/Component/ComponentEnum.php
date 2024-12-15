<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

enum ComponentEnum: string
{
    case PM = 'pm';
    case SERVERTIME_AND_VERSION = 'servertime';
    case RESEARCH = 'research';
    case COLONIES = 'colonies';
    case USER = 'user';

    public function getTemplate(): string
    {
        return match ($this) {
            self::PM => 'html/game/component/pmComponent.twig',
            self::SERVERTIME_AND_VERSION => 'html/game/component/serverTimeAndVersionComponent.twig',
            self::RESEARCH => 'html/game/component/researchComponent.twig',
            self::COLONIES => 'html/game/component/coloniesComponent.twig',
            self::USER => 'html/game/component/userComponent.twig',
        };
    }

    public function getRefreshIntervalInSeconds(): ?int
    {
        return match ($this) {
            self::PM => 60,
            self::SERVERTIME_AND_VERSION => 300,
            self::RESEARCH => null,
            self::COLONIES => null,
            self::USER => null,
        };
    }
}
