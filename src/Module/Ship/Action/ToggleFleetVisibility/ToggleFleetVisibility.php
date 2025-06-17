<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ToggleFleetVisibility;

use Override;
use request;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\Noop\Noop;

final class ToggleFleetVisibility implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TOGGLE_FLEET';

    private const string SESSION_KEY = 'hiddenshiplistfleets';

    public function __construct(private readonly SessionStorageInterface $sessionStorage) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $fleetId = request::getIntFatal('fleet');

        if ($this->sessionStorage->hasSessionValue(self::SESSION_KEY, $fleetId)) {
            $this->sessionStorage->deleteSessionData(self::SESSION_KEY, $fleetId);
        } else {
            $this->sessionStorage->storeSessionData(self::SESSION_KEY, $fleetId);
        }
        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
