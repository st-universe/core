<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivatePhaser;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class FleetActivatePhaser implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_PHASER';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $this->helper->activateFleet(request::indInt('id'), SpacecraftSystemTypeEnum::PHASER, $game);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
