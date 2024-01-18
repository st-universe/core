<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\RefreshColonyEps;

use request;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class RefreshColonyEps implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'REFRESH_COLONY_EPS';

    private ColonyLoaderInterface $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::getIntFatal('id'),
            $game->getUser()->getId(),
            false
        );

        $game->showMacro('html/macros.xhtml/table_cell');

        $game->setTemplateVar('ID', 'current_energy');
        $game->setTemplateVar('INNERHTML', $colony->getEps());
    }
}
