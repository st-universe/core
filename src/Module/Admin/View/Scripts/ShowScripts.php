<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Scripts;

use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PirateSetupRepositoryInterface;

final class ShowScripts implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SCRIPTS';

    public function __construct(
        private PirateSetupRepositoryInterface $pirateSetupRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/scripts.twig');
        $game->appendNavigationPart('/admin/?SHOW_SCRIPTS=1', _('Scripts'));
        $game->setPageTitle(_('Scripts'));

        $game->setTemplateVar('DEFAULT_LAYER', MapEnum::DEFAULT_LAYER);
        $game->setTemplateVar('PIRATE_SETUPS', $this->pirateSetupRepository->getAllOrderedByName());
    }
}
