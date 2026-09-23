<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchivePlot;

use Stu\Component\Communication\Kn\KnArchiveFactoryInterface;
use Stu\Component\Communication\Kn\KnArchiveItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Lib\Paging\PagingFactory;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;
use Stu\Orm\Repository\RpgPlotArchivRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberArchivRepositoryInterface;

final class ShowKnArchivePlot implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ARCHIVE_PLOT';

    public function __construct(
        private ShowKnArchivePlotRequestInterface $showKnArchivePlotRequest,
        private KnPostArchivRepositoryInterface $knPostArchivRepository,
        private RpgPlotArchivRepositoryInterface $rpgPlotArchivRepository,
        private RpgPlotMemberArchivRepositoryInterface $rpgPlotMemberArchivRepository,
        private KnArchiveFactoryInterface $knArchiveFactory,
        private PagingFactory $pagingFactory
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $user = $game->getUser();
        $plotId = $this->showKnArchivePlotRequest->getPlotId();

        $plot = $this->rpgPlotArchivRepository->findOneBy(['former_id' => $plotId]);
        $game->setViewTemplate('html/communication/plotArchivDetails.twig');
        if ($plot === null) {
            return;
        }
        $game->setPageTitle(sprintf('Archiv-Plot: %s', $plot->getTitle()));

        $mark = $this->showKnArchivePlotRequest->getKnOffset();

        if ($mark % GameEnum::KN_PER_SITE !== 0 || $mark < 0) {
            $mark = 0;
        }

        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_KN_ARCHIVE=1&version=' . $plot->getVersion(), _('Archiv'));
        $game->appendNavigationPart(
            sprintf(
                'comm.php?%s=1&plotid=%d',
                self::VIEW_IDENTIFIER,
                $plot->getFormerId()
            ),
            $plot->getTitle()
        );

        $archivePosts = $this->knPostArchivRepository->getByPlotFormerId($plot->getFormerId(), $mark, GameEnum::KN_PER_SITE);

        $posts = array_map(
            fn (KnPostArchiv $post): KnArchiveItemInterface => $this->knArchiveFactory->createKnArchiveItem($post, $user, $plot),
            $archivePosts
        );

        $plotMembers = $this->rpgPlotMemberArchivRepository->getByPlotFormerId($plot->getFormerId());

        $game->setTemplateVar('KN_POSTINGS', $posts);
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('PLOT', $plot);
        $game->setTemplateVar('PLOT_MEMBERS', $plotMembers);
        $game->setTemplateVar('ARCHIVE_VERSION', $plot->getVersion() ?? '');
        $game->setTemplateVar('ARCHIVE_VERSION_DISPLAY', $this->formatVersion($plot->getVersion() ?? ''));
        $game->setTemplateVar('PAGING', $this->pagingFactory->createPaging(
            $this->knPostArchivRepository->getAmountByPlot($plot->getFormerId()),
            GameEnum::KN_PER_SITE,
            $mark,
            sprintf('?%s=1&plotid=%d&mark=%%d', self::VIEW_IDENTIFIER, $plot->getFormerId())
        ));
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
