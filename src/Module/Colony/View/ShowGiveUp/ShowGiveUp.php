<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowGiveUp;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowGiveUp implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_GIVEUP_AJAX';

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyRepository->find(request::indInt('id'));

        $code = substr(md5($colony->getName()), 0, 6);

        $game->setPageTitle(_('Kolonie aufgeben'));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/giveup');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('GIVE_UP_CODE', $code);
    }
}
