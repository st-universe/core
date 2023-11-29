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

final class ShowPostIdSearchResult implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_POSTID_SEARCH';

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

        $id = $this->showSearchResultRequest->getSearchId();
        if ($id == 0) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->addInformation('Bitte eine Beitrag-ID angeben!');

            return;
        }

        $post = $this->knPostRepository->find($id);

        if ($post === null) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->addInformation(sprintf('Es existiert kein Beitrag mit der ID %d', $id));

            return;
        }

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                fn (KnPostInterface $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                [$post]
            )
        );
    }
}
