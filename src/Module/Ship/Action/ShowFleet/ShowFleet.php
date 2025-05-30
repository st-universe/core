<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ShowFleet;

use Override;
use request;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\Noop\Noop;

final class ShowFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SHOW_FLEET';

    public function __construct(private SessionInterface $session) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $fleetId = request::getIntFatal('fleet');
        $this->session->deleteSessionData('hiddenfleets', $fleetId);
        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
