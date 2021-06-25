<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSearchResult;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowUserSearchResult implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_USER_SEARCH';

    private ShowSearchResultRequestInterface $showSearchResultRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private KnFactoryInterface $knFactory;

    public function __construct(
        ShowSearchResultRequestInterface $showSearchResultRequest,
        KnPostRepositoryInterface $knPostRepository,
        KnFactoryInterface $knFactory
    ) {
        $this->showSearchResultRequest = $showSearchResultRequest;
        $this->knPostRepository = $knPostRepository;
        $this->knFactory = $knFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($this->showSearchResultRequest->getSearchId() == 0) {
            return;
        }

        $game->setPageTitle(_('Kommunikationsnetzwerk'));
        $game->setTemplateFile('html/comm.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                function (KnPostInterface $knPost) use ($user): KnItemInterface {
                    return $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                },
                $this->knPostRepository->getByUser($this->showSearchResultRequest->getSearchId())
            )
        );
    }
}
