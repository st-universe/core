<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ToggleFleetVisibility;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\View\Noop\Noop;

final class ToggleFleetVisibility implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TOGGLE_FLEET';

    private const SESSION_KEY = 'hiddenshiplistfleets';

    private SessionInterface $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    public function handle(GameControllerInterface $game): void
    {
        $fleetId = request::getIntFatal('fleet');

        if ($this->session->hasSessionValue(self::SESSION_KEY, $fleetId)) {
            $this->session->deleteSessionData(self::SESSION_KEY, $fleetId);
        } else {
            $this->session->storeSessionData(self::SESSION_KEY, $fleetId);
        }
        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
