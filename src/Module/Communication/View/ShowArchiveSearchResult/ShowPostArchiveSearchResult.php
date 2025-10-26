<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowArchiveSearchResult;

use Stu\Component\Communication\Kn\KnArchiveFactoryInterface;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;

final class ShowPostArchiveSearchResult implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_POST_ARCHIVE_SEARCH';

    public const int MINIMUM_SEARCH_WORD_LENGTH = 3;

    public function __construct(
        private ShowArchiveSearchResultRequestInterface $showArchiveSearchResultRequest,
        private KnPostArchivRepositoryInterface $knPostArchivRepository,
        private KnArchiveFactoryInterface $knArchiveFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $version = $this->showArchiveSearchResultRequest->getVersion();
        $searchString = $this->showArchiveSearchResultRequest->getSearchString();

        $game->setViewTemplate('html/communication/knArchiv.twig');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_KN_ARCHIVE=1&version=' . $version, _('Archiv'));
        $game->setPageTitle(sprintf('Archiv-Suche - Version %s', $version));

        if ($version === '') {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->getInfo()->addInformation('Ungültige Archiv-Version!');
            return;
        }

        if (strlen($searchString) < self::MINIMUM_SEARCH_WORD_LENGTH) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->getInfo()->addInformation(sprintf('Der Suchbegriff muss mindestens %d Zeichen lang sein!', self::MINIMUM_SEARCH_WORD_LENGTH));
            return;
        }

        $posts = $this->knPostArchivRepository->searchByContentAndVersion($searchString, $version);
        $game->getInfo()->addInformation(sprintf('Es wurden %d Beiträge gefunden', count($posts)));

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
            $knPostings[] = $this->knArchiveFactory->createKnArchiveItem($post, $user, $plot);
        }

        $game->setTemplateVar('KN_POSTINGS', $knPostings);
        $game->setTemplateVar('ARCHIVE_VERSION', $version);
        $game->setTemplateVar('TOTAL_POSTS', $this->knPostArchivRepository->getAmountByVersion($version));
        $game->setTemplateVar('SHOW_ARCHIVE_VIEW', 'SHOW_KN_ARCHIVE');
        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
