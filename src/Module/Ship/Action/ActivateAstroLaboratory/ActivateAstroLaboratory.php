<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateAstroLaboratory;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class ActivateAstroLaboratory implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACTIVATE_ASTRO';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $this->helper->activate(request::indInt('id'), SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY, $game);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
