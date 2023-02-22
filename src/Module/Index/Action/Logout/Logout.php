<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Logout;

use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

/**
 * Performs a logout for the user
 */
final class Logout implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOGOUT';

    private SessionInterface $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    public function handle(GameControllerInterface $game): void
    {
        if ($game->hasUser()) {
            $this->session->logout();
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
