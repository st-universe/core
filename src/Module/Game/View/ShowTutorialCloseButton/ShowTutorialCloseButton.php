<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowTutorialCloseButton;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Component\Faction\FactionEnum;

final class ShowTutorialCloseButton implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TUTORIAL_CLOSE';

    public function handle(GameControllerInterface $game): void
    {
        if ($game->getUser()->getFactionId() === FactionEnum::FACTION_ROMULAN) {
            $game->setTemplateFile('html/tutorial/closebutton2.twig');
        } elseif ($game->getUser()->getFactionId() === FactionEnum::FACTION_KLINGON) {
            $game->setTemplateFile('html/tutorial/closebutton3.twig');
        } elseif ($game->getUser()->getFactionId() === FactionEnum::FACTION_CARDASSIAN) {
            $game->setTemplateFile('html/tutorial/closebutton4.twig');
        } else {
            $game->setTemplateFile('html/tutorial/closebutton1.twig');
        }
    }
}
