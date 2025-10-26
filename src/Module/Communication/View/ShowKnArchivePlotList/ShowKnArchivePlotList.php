<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchivePlotList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RpgPlotArchivRepositoryInterface;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberArchivRepositoryInterface;

final class ShowKnArchivePlotList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ARCHIVE_PLOTLIST';

    public function __construct(
        private ShowKnArchivePlotListRequestInterface $showKnArchivePlotListRequest,
        private RpgPlotArchivRepositoryInterface $rpgPlotArchivRepository,
        private KnPostArchivRepositoryInterface $knPostArchivRepository,
        private RpgPlotMemberArchivRepositoryInterface $rpgPlotMemberArchivRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $version = $this->showKnArchivePlotListRequest->getVersion();

        $game->setViewTemplate('html/communication/plot/plotsArchiv.twig');

        if ($version === '') {
            return;
        }

        $game->setPageTitle(sprintf('Archiv-Plots - Version %s', $this->formatVersion($version)));
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_KN_ARCHIVE=1&version=' . $version, _('Archiv'));
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1&version=%s', self::VIEW_IDENTIFIER, $version),
            _('Plots')
        );

        $plots = $this->rpgPlotArchivRepository->getOrderedListByVersion($version);

        $active_plots = [];
        $ended_plots = [];

        foreach ($plots as $plot) {
            $postCount = $this->knPostArchivRepository->getAmountByPlot($plot->getFormerId());
            $members = $this->rpgPlotMemberArchivRepository->getByPlotFormerId($plot->getFormerId());

            $plotData = [
                'plot' => $plot,
                'postCount' => $postCount,
                'memberCount' => count($members),
                'members' => $members
            ];

            if ($plot->isActive()) {
                $active_plots[] = $plotData;
            } else {
                $ended_plots[] = $plotData;
            }
        }

        $game->setTemplateVar('ACTIVE_PLOTS', $active_plots);
        $game->setTemplateVar('ENDED_PLOTS', $ended_plots);
        $game->setTemplateVar('ARCHIVE_VERSION', $version);
        $game->setTemplateVar('ARCHIVE_VERSION_DISPLAY', $this->formatVersion($version));
        $game->setTemplateVar('SHOW_ARCHIVE_VIEW', 'SHOW_KN_ARCHIVE');
    }

    private function formatVersion(string $version): string
    {
        $cleanVersion = ltrim($version, 'v');

        if (str_contains($cleanVersion, 'alpha')) {
            return 'v' . str_replace('alpha', 'α', $cleanVersion);
        }

        if (preg_match('/^(\d)(\d)$/', $cleanVersion, $matches)) {
            return 'v' . $matches[1] . '.' . $matches[2];
        }

        return $version;
    }
}
