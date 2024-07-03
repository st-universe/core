<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowGiveUp;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowGiveUp implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_GIVEUP_AJAX';

    public function __construct(private ColonyRepositoryInterface $colonyRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyRepository->find(request::indInt('id'));

        $code = substr(md5($colony->getName()), 0, 6);

        $game->setPageTitle(_('Kolonie aufgeben'));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/giveup');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('GIVE_UP_CODE', $code);
    }
}
