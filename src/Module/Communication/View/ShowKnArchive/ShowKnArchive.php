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
            $knNavigation[] = [
                "page" => "<<",
                "mark" => 0,
                "cssclass" => "pages",
                "style" => "min-width: 30px; width: auto; padding: 5px 8px; text-align: center; display: inline-block; white-space: nowrap;"
            ];
            $knNavigation[] = [
                "page" => "<",
                "mark" => ($mark - GameEnum::KN_PER_SITE),
                "cssclass" => "pages",
                "style" => "min-width: 30px; width: auto; padding: 5px 8px; text-align: center; display: inline-block; white-space: nowrap;"
            ];
        }

        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $knNavigation[] = [
                "page" => $i,
                "mark" => ($i * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE),
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages"),
                "style" => "min-width: 30px; width: auto; padding: 5px 8px; text-align: center; display: inline-block; white-space: nowrap;"
            ];
        }

        if ($curpage + 1 !== $maxpage) {
            $knNavigation[] = [
                "page" => ">",
                "mark" => ($mark + GameEnum::KN_PER_SITE),
                "cssclass" => "pages",
                "style" => "min-width: 30px; width: auto; padding: 5px 8px; text-align: center; display: inline-block; white-space: nowrap;"
            ];
            $knNavigation[] = [
                "page" => ">>",
                "mark" => $maxpage * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE,
                "cssclass" => "pages",
                "style" => "min-width: 30px; width: auto; padding: 5px 8px; text-align: center; display: inline-block; white-space: nowrap;"
            ];
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
            $archiveItem = $this->knArchiveFactory->createKnArchiveItem($post, $game->getUser(), $plot);

            $posts[] = [
                'id' => $archiveItem->getId(),
                'username' => $archiveItem->getUsername(),
                'title' => $archiveItem->getTitle(),
                'text' => $this->sanitizeHtml($archiveItem->getText()),
                'date' => $archiveItem->getDate(),
                'editDate' => $archiveItem->getEditDate(),
                'formerId' => $archiveItem->getFormerId(),
                'refs' => $archiveItem->getRefs(),
                'ratingBar' => $archiveItem->getRatingBar(),
                'commentCount' => $archiveItem->getCommentCount(),
                'divClass' => $archiveItem->getDivClass(),
                'rpgPlot' => $archiveItem->getRpgPlot(),
                'formattedDate' => $this->formatDateForVersion($archiveItem->getDate(), $version),
                'formattedEditDate' => $archiveItem->getEditDate() > 0 ? $this->formatDateForVersion($archiveItem->getEditDate(), $version) : null,
            ];
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

    private function formatDateForVersion(int $timestamp, string $version): string
    {
        $yearsToAdd = $this->getYearsForVersion($version);

        $date = new \DateTime('@' . $timestamp);
        $date->modify('+' . $yearsToAdd . ' years');

        return $date->format('d.m.Y H:i');
    }

    private function getYearsForVersion(string $version): int
    {
        $cleanVersion = ltrim($version, 'v');

        return match (true) {
            str_contains($cleanVersion, 'alpha') || str_starts_with($cleanVersion, '3') => 370,
            str_starts_with($cleanVersion, '26') => 374,
            str_starts_with($cleanVersion, '15') => 375,
            default => 373
        };
    }

    private function sanitizeHtml(string $html): string
    {
        if (empty(trim($html))) {
            return $html;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="UTF-8">' .
                '<div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();

        $body = $dom->getElementsByTagName('div')->item(0);
        if ($body === null) {
            return $html;
        }

        $result = '';
        foreach ($body->childNodes as $node) {
            $result .= $dom->saveHTML($node);
        }

        return $result;
    }
}
