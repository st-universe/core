<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditPlot;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowEditPlot implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_EDIT_PLOT';

    public function __construct(private ShowEditPlotRequestInterface $showEditPlotRequest, private RpgPlotRepositoryInterface $rpgPlotRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        /** @var RpgPlotInterface $plot */
        $plot = $this->rpgPlotRepository->find($this->showEditPlotRequest->getPlotId());
        if ($plot === null || $plot->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $game->setViewTemplate('html/communication/plot/editPlot.twig');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1&plotid=%d', static::VIEW_IDENTIFIER, $plot->getId()),
            _('Plot editiren')
        );
        $game->setPageTitle(_('Plot editieren'));

        $game->setTemplateVar('PLOT', $plot);
    }
}
