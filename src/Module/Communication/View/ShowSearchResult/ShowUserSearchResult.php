<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSearchResult;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\ModuleViewEnum;
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

        $game->setViewTemplate(ModuleViewEnum::COMMUNICATION->getTemplate());
        $game->appendNavigationPart('comm.php', _('KommNet'));

        if ($this->showSearchResultRequest->getSearchId() == 0) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->addInformation('Bitte eine Spieler-ID angeben!');

            return;
        }

        $posts = $this->knPostRepository->getByUser($this->showSearchResultRequest->getSearchId());

        $game->addInformation(sprintf('Es wurden %d Beiträge gefunden', count($posts)));

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                fn (KnPostInterface $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $posts
            )
        );
    }
}
