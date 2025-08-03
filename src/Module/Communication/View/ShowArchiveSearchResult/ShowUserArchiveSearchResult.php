<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowArchiveSearchResult;

use Override;
use Stu\Component\Communication\Kn\KnArchiveFactoryInterface;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;

final class ShowUserArchiveSearchResult implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_USER_ARCHIVE_SEARCH';

    public function __construct(
        private ShowArchiveSearchResultRequestInterface $showArchiveSearchResultRequest,
        private KnPostArchivRepositoryInterface $knPostArchivRepository,
        private KnArchiveFactoryInterface $knArchiveFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $version = $this->showArchiveSearchResultRequest->getVersion();
        $searchId = $this->showArchiveSearchResultRequest->getSearchId();

        $game->setViewTemplate('html/communication/knArchiv.twig');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_KN_ARCHIVE=1&version=' . $version, _('Archiv'));
        $game->setPageTitle(sprintf('Archiv-Suche - Version %s', $version));

        if ($version === '') {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->addInformation('Ungültige Archiv-Version!');
            return;
        }

        if ($searchId == 0) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->addInformation('Bitte eine Spieler-ID angeben!');
            return;
        }

        $posts = $this->knPostArchivRepository->getByUserAndVersion($searchId, $version);
        $game->addInformation(sprintf('Es wurden %d Beiträge gefunden', count($posts)));

        $plotIds = [];
        foreach ($posts as $post) {
            if ($post->getPlotId()) {
                $plotIds[] = $post->getPlotId();
            }
        }

        $plots = [];
        if (!empty($plotIds)) {
            $plots = $this->knPostArchivRepository->getPlotsByIds($plotIds);
        }

        $knPostings = [];
        foreach ($posts as $post) {
            $plot = null;
            if ($post->getPlotId() && isset($plots[$post->getPlotId()])) {
                $plot = $plots[$post->getPlotId()];
            }
            $archiveItem = $this->knArchiveFactory->createKnArchiveItem($post, $user, $plot);

            $knPostings[] = [
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

        $game->setTemplateVar('KN_POSTINGS', $knPostings);
        $game->setTemplateVar('ARCHIVE_VERSION', $version);
        $game->setTemplateVar('TOTAL_POSTS', $this->knPostArchivRepository->getAmountByVersion($version));
        $game->setTemplateVar('SHOW_ARCHIVE_VIEW', 'SHOW_KN_ARCHIVE');
        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
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
