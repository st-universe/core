<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWriteKn;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowWriteKn implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'WRITE_KN';

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    public function __construct(
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/writekn.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER),
            _('Beitrag schreiben')
        );
        $game->setPageTitle("Beitrag schreiben");

        $game->setTemplateVar(
            'ACTIVE_RPG_PLOTS',
            $this->rpgPlotRepository->getActiveByUser($game->getUser()->getId())
        );
    }
}
