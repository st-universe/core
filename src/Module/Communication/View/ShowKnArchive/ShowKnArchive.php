<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchive;

use Override;
use request;
use Stu\Component\Communication\Kn\KnArchiveFactoryInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;

final class ShowKnArchive implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_KN_ARCHIVE';

    public function __construct(
        private ShowKnArchiveRequestInterface $showKnArchiveRequest,
        private KnPostArchivRepositoryInterface $knPostArchivRepository,
        private KnArchiveFactoryInterface $knArchiveFactory,
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $version = $this->showKnArchiveRequest->getVersion();
        $game->setViewTemplate('html/communication/knArchiv.twig');
        if ($version === '') {
            return;
        }
        $game->setPageTitle(sprintf('KN-Archiv - Version %s', $this->formatVersion($version)));
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_KN_ARCHIVE=1&version=' . $version, _('Archiv'));

        $knPostCount = $this->knPostArchivRepository->getAmountByVersion($version);

        $mark = $knPostCount;
        $lim = floor($mark / GameEnum::KN_PER_SITE) * GameEnum::KN_PER_SITE;
        $knStart = $mark % GameEnum::KN_PER_SITE == 0 ? $lim - GameEnum::KN_PER_SITE : $lim;

        $mark = request::getInt('mark');
        if ($mark % GameEnum::KN_PER_SITE != 0 || $mark < 0) {
            $mark = 0;
        }

        $maxpage = ceil($knPostCount / GameEnum::KN_PER_SITE);
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

        $archivePosts = $this->knPostArchivRepository->getByVersion($version, $mark, GameEnum::KN_PER_SITE);

        $plotIds = [];
        foreach ($archivePosts as $post) {
            if ($post->getPlotId()) {
                $plotIds[] = $post->getPlotId();
            }
        }

        $plots = [];
        if (!empty($plotIds)) {
            $plots = $this->knPostArchivRepository->getPlotsByIds($plotIds);
        }

        $posts = [];
        foreach ($archivePosts as $post) {
            $plot = null;
            if ($post->getPlotId() && isset($plots[$post->getPlotId()])) {
                $plot = $plots[$post->getPlotId()];
            }
            $posts[] = $this->knArchiveFactory->createKnArchiveItem($post, $game->getUser(), $plot);
        }

        $game->setTemplateVar('KN_POSTINGS', $posts);
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
        $game->setTemplateVar('KN_START', $knStart);
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('ARCHIVE_VERSION', $version);
        $game->setTemplateVar('ARCHIVE_VERSION_DISPLAY', $this->formatVersion($version));
        $game->setTemplateVar('TOTAL_POSTS', $knPostCount);
        $game->setTemplateVar('SHOW_ARCHIVE_VIEW', self::VIEW_IDENTIFIER);
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
