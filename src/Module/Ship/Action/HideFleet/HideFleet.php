<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\HideFleet;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\View\Noop\Noop;

final class HideFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_HIDE_FLEET';

    private SessionInterface $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    public function handle(GameControllerInterface $game): void
    {
        $fleetId = request::getIntFatal('fleet');
        $this->session->storeSessionData('hiddenfleets', $fleetId);
        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
