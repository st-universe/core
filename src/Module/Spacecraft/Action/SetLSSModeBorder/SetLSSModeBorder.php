<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SetLSSModeBorder;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class SetLSSModeBorder implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_LSS_BORDER';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $this->helper->setLssMode(request::indInt('id'), SpacecraftLssModeEnum::BORDER, $game);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
