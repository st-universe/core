<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SetLSSMode;

use request;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class SetLSSMode implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_LSS_MODE';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $modeInt = request::indInt('mode');

        if (!SpacecraftLssModeEnum::tryFrom($modeInt)) {
            return;
        }

        $mode = SpacecraftLssModeEnum::from($modeInt);

        $this->helper->setLssMode(request::indInt('id'), $mode, $game);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
