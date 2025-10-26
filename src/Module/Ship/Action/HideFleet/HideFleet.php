<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\HideFleet;

use request;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\Noop\Noop;

final class HideFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_HIDE_FLEET';

    public function __construct(private readonly SessionStorageInterface $sessionStorage) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $fleetId = request::getIntFatal('fleet');
        $this->sessionStorage->storeSessionData('hiddenfleets', $fleetId);
        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
