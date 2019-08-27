<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ShowFleet;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\View\Noop\Noop;

final class ShowFleet implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SHOW_FLEET';

    private $session;

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
