<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowArchiveSearchResult;

use Override;
use Stu\Component\Communication\Kn\KnArchiveFactoryInterface;
use Stu\Component\Communication\Kn\KnArchiveItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostArchiv;
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
            $knPostings[] = $this->knArchiveFactory->createKnArchiveItem($post, $user, $plot);
        }

        $game->setTemplateVar('KN_POSTINGS', $knPostings);
        $game->setTemplateVar('ARCHIVE_VERSION', $version);
        $game->setTemplateVar('TOTAL_POSTS', $this->knPostArchivRepository->getAmountByVersion($version));
        $game->setTemplateVar('SHOW_ARCHIVE_VIEW', 'SHOW_KN_ARCHIVE');
        $game->addExecuteJS("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
