<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\RefreshColonyEps;

use Override;
use request;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class RefreshColonyEps implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'REFRESH_COLONY_EPS';

    public function __construct(private ColonyLoaderInterface $colonyLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::getIntFatal('id'),
            $game->getUser()->getId(),
            false
        );

        $game->showMacro('html/colony/component/tableCell.twig');

        $game->setTemplateVar('ID', 'current_energy');
        $game->setTemplateVar('INNERHTML', $colony->getChangeable()->getEps());
    }
}
