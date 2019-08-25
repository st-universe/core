<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditPlot;

use AccessViolation;
use RPGPlot;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowEditPlot implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EDIT_PLOT';

    private $showEditPlotRequest;

    public function __construct(
        ShowEditPlotRequestInterface $showEditPlotRequest
    ) {
        $this->showEditPlotRequest = $showEditPlotRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = new RPGPlot($this->showEditPlotRequest->getPlotId());
        if ($plot->getUserId() != $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $game->setTemplateFile('html/editplot.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1&plotid=%d', static::VIEW_IDENTIFIER, $plot->getId()),
            _('Plot editiren')
        );
        $game->setPageTitle(_('Plot editieren'));

        $game->setTemplateVar('PLOT', $plot);
    }
}
