<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ToggleFleetVisibility;

use Override;
use request;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\Noop\Noop;

final class ToggleFleetVisibility implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TOGGLE_FLEET';

    private const string SESSION_KEY = 'hiddenshiplistfleets';

    public function __construct(private SessionInterface $session)
    {
    }

    #[Override]
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
