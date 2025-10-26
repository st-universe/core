<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowGiveUp;

use request;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowGiveUp implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_GIVEUP_AJAX';

    public function __construct(private ColonyLoaderInterface $colonyLoader) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId(),
            false
        );

        $code = substr(md5($colony->getName()), 0, 6);

        $game->setPageTitle(_('Kolonie aufgeben'));
        $game->setMacroInAjaxWindow('html/colony/component/giveup.twig');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('GIVE_UP_CODE', $code);
    }
}
