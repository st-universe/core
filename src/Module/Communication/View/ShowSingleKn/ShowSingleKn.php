<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSingleKn;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowSingleKn implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SINGLE_KN';

    private ShowSingleKnRequestInterface $showSingleKnRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private KnFactoryInterface $knFactory;

    public function __construct(
        ShowSingleKnRequestInterface $showSingleKnRequest,
        KnPostRepositoryInterface $knPostRepository,
        KnFactoryInterface $knFactory
    ) {
        $this->showSingleKnRequest = $showSingleKnRequest;
        $this->knPostRepository = $knPostRepository;
        $this->knFactory = $knFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $post = $this->knPostRepository->find($this->showSingleKnRequest->getPostId());

        $game->setPageTitle(_('Kommunikationsnetzwerk'));
        $game->setViewTemplate(ModuleViewEnum::COMMUNICATION->getTemplate());
        $game->appendNavigationPart('comm.php', _('KommNet'));

        $knPostings = [];
        if ($post !== null) {
            $knPostings[] = $this->knFactory->createKnItem(
                $post,
                $user
            );
        } else {
            $game->addInformation('Dieser Beitrag existiert nicht mehr');
        }

        $game->setTemplateVar('KN_POSTINGS', $knPostings);
    }
}
