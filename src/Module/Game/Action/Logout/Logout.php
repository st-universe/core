<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\Logout;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

/**
 * Performs a logout for the user
 */
final class Logout implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LOGOUT';

    public function __construct(private SessionInterface $session) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        if ($game->hasUser()) {
            $this->session->logout();
        }

        $game->redirectTo(sprintf('/%s.php', ModuleEnum::INDEX->value));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
