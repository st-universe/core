<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetAlertGreen;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\AlertStateManagerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class FleetAlertGreen implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_ALERT_GREEN';

    public function __construct(private readonly AlertStateManagerInterface $alertStateManager) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $this->alertStateManager->setAlertStateFleet(request::indInt('id'), SpacecraftAlertStateEnum::ALERT_GREEN);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
