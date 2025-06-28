<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchivePlot;

use Override;
use Stu\Component\Communication\Kn\KnArchiveFactoryInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
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
        private KnArchiveFactoryInterface $knArchiveFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
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

        if ($mark % GameEnum::KN_PER_SITE != 0 || $mark < 0) {
            $mark = 0;
        }

        $maxcount = $this->knPostArchivRepository->getAmountByPlot($plot->getFormerId());
        $maxpage = ceil($maxcount / GameEnum::KN_PER_SITE);
        $curpage = floor($mark / GameEnum::KN_PER_SITE);

        $knNavigation = [];
        if ($curpage != 0) {
            $knNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $knNavigation[] = ["page" => "<", "mark" => ($mark - GameEnum::KN_PER_SITE), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $knNavigation[] = [
                "page" => $i,
                "mark" => ($i * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE),
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 !== $maxpage) {
            $knNavigation[] = ["page" => ">", "mark" => ($mark + GameEnum::KN_PER_SITE), "cssclass" => "pages"];
            $knNavigation[] = ["page" => ">>", "mark" => $maxpage * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE, "cssclass" => "pages"];
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
            fn($post) => $this->knArchiveFactory->createKnArchiveItem($post, $user, $plot),
            $archivePosts
        );

        $plotMembers = $this->rpgPlotMemberArchivRepository->getByPlotFormerId($plot->getFormerId());

        $game->setTemplateVar('KN_POSTINGS', $posts);
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
        $game->setTemplateVar('PLOT', $plot);
        $game->setTemplateVar('PLOT_MEMBERS', $plotMembers);
        $game->setTemplateVar('ARCHIVE_VERSION', $plot->getVersion() ?? '');
        $game->setTemplateVar('ARCHIVE_VERSION_DISPLAY', $this->formatVersion($plot->getVersion() ?? ''));
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
