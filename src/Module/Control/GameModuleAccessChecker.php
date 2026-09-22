<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;

final class GameModuleAccessChecker implements GameModuleAccessCheckerInterface
{
    public function __construct(
        private readonly GameUserRoleCheckerInterface $gameUserRoleChecker
    ) {}

    #[\Override]
    public function isAllowed(ModuleEnum $module): bool
    {
        return match ($module) {
            ModuleEnum::NPC => $this->gameUserRoleChecker->isNpc() || $this->gameUserRoleChecker->isAdmin(),
            ModuleEnum::ADMIN => $this->gameUserRoleChecker->isAdmin(),
            default => true,
        };
    }
}
