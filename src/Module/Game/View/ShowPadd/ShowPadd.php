<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowPadd;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Component\Faction\FactionEnum;

final class ShowPadd implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PADD';

    public function handle(GameControllerInterface $game): void
    {
        if ($game->getUser()->getFactionId() === FactionEnum::FACTION_ROMULAN) {
            $game->setTemplateFile('html/tutorial/padd2.twig');
        } elseif ($game->getUser()->getFactionId() === FactionEnum::FACTION_KLINGON) {
            $game->setTemplateFile('html/tutorial/padd3.twig');
        } else {
            $game->setTemplateFile('html/tutorial/padd1.twig');
        }
    }
}