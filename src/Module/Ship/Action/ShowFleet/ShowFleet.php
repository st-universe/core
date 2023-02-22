<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ShowFleet;

use request;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\Noop\Noop;

final class ShowFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SHOW_FLEET';

    private SessionInterface $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    public function handle(GameControllerInterface $game): void
    {
        $fleetId = request::getIntFatal('fleet');
        $this->session->deleteSessionData('hiddenfleets', $fleetId);
        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
