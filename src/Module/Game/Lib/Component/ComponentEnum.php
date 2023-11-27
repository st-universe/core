<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

enum ComponentEnum: string
{
    case PM_NAVLET = 'pm';
    case SERVERTIME_NAVLET = 'servertime';
    case RESEARCH_NAVLET = 'research';
    case COLONIES_NAVLET = 'colonies';
    case USER_NAVLET = 'user';

    public function getTemplate(): string
    {
        return match ($this) {
            self::PM_NAVLET => 'html/game/component/pmNavlet.twig',
            self::SERVERTIME_NAVLET => 'html/game/component/serverTimeAndVersion.twig',
            self::RESEARCH_NAVLET => 'html/game/component/researchNavlet.twig',
            self::COLONIES_NAVLET => 'html/game/component/coloniesNavlet.twig',
            self::USER_NAVLET => 'html/game/component/userNavlet.twig',
        };
    }

    public function getRefreshIntervalInSeconds(): ?int
    {
        return match ($this) {
            self::PM_NAVLET => 60,
            self::SERVERTIME_NAVLET => 300,
            self::RESEARCH_NAVLET => null,
            self::COLONIES_NAVLET => null,
            self::USER_NAVLET => null,
        };
    }
}
