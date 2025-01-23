<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowTutorialCloseButton;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Component\Faction\FactionEnum;

final class ShowTutorialCloseButton implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TUTORIAL_CLOSE';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $factionId = $game->getUser()->getFactionId();
        $templateFile = sprintf('html/tutorial/closebutton%d.twig', $factionId);
        $game->setTemplateFile($templateFile);
    }
}
