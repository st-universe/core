<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSearchResult;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowPostIdSearchResult implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_POSTID_SEARCH';

    public function __construct(private ShowSearchResultRequestInterface $showSearchResultRequest, private KnPostRepositoryInterface $knPostRepository, private KnFactoryInterface $knFactory) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->setViewTemplate(ModuleEnum::COMMUNICATION->getTemplate());
        $game->appendNavigationPart('comm.php', _('KommNet'));

        $id = $this->showSearchResultRequest->getSearchId();
        if ($id == 0) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->getInfo()->addInformation('Bitte eine Beitrag-ID angeben!');

            return;
        }

        $post = $this->knPostRepository->findActiveById($id);

        if ($post === null) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->getInfo()->addInformation(sprintf('Es existiert kein Beitrag mit der ID %d', $id));

            return;
        }

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                fn(KnPost $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                [$post]
            )
        );

        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
