<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;

final class GameModuleAccessChecker implements GameModuleAccessCheckerInterface
{
    #[\Override]
    public function isAllowed(ModuleEnum $module, GameControllerInterface $game): bool
    {
        return match ($module) {
            ModuleEnum::NPC => $game->isNpc() || $game->isAdmin(),
            ModuleEnum::ADMIN => $game->isAdmin(),
            default => true,
        };
    }
}
