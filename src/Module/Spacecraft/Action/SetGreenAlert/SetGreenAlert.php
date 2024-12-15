<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SetGreenAlert;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class SetGreenAlert implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_GREEN_ALERT';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $this->helper->setAlertState(request::indInt('id'), SpacecraftAlertStateEnum::ALERT_GREEN, $game);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
