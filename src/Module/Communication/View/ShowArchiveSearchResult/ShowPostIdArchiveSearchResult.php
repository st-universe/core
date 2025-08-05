<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowArchiveSearchResult;

use Override;
use Stu\Component\Communication\Kn\KnArchiveFactoryInterface;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;

final class ShowPostIdArchiveSearchResult implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_POSTID_ARCHIVE_SEARCH';

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
            $game->getInfo()->addInformation('Ungültige Archiv-Version!');
            return;
        }

        if ($searchId == 0) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->getInfo()->addInformation('Bitte eine Original-ID angeben!');
            return;
        }

        $post = $this->knPostArchivRepository->findByFormerIdAndVersion($searchId, $version);

        if ($post === null) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->getInfo()->addInformation(sprintf('Es existiert kein Beitrag mit der Original-ID %d in Version %s', $searchId, $version));
            return;
        }

        $plot = null;
        if ($post->getPlotId()) {
            $plots = $this->knPostArchivRepository->getPlotsByIds([$post->getPlotId()]);
            if (isset($plots[$post->getPlotId()])) {
                $plot = $plots[$post->getPlotId()];
            }
        }

        $knPostings = [$this->knArchiveFactory->createKnArchiveItem($post, $user, $plot)];

        $game->setTemplateVar('KN_POSTINGS', $knPostings);
        $game->setTemplateVar('ARCHIVE_VERSION', $version);
        $game->setTemplateVar('TOTAL_POSTS', $this->knPostArchivRepository->getAmountByVersion($version));
        $game->setTemplateVar('SHOW_ARCHIVE_VIEW', 'SHOW_KN_ARCHIVE');
        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
