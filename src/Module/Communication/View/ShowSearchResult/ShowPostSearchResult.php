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

final class ShowPostSearchResult implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_POST_SEARCH';

    public const int MINIMUM_SEARCH_WORD_LENGTH = 3;

    public function __construct(private ShowSearchResultRequestInterface $showSearchResultRequest, private KnPostRepositoryInterface $knPostRepository, private KnFactoryInterface $knFactory) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->setViewTemplate(ModuleEnum::COMMUNICATION->getTemplate());
        $game->appendNavigationPart('comm.php', _('KommNet'));

        if (strlen($this->showSearchResultRequest->getSearchString()) < self::MINIMUM_SEARCH_WORD_LENGTH) {
            $game->setTemplateVar('KN_POSTINGS', null);
            $game->getInfo()->addInformation(sprintf('Der Suchbegriff muss mindestens %d Zeichen lang sein!', self::MINIMUM_SEARCH_WORD_LENGTH));

            return;
        }

        $posts = $this->knPostRepository->searchByContent($this->showSearchResultRequest->getSearchString());

        $game->getInfo()->addInformation(sprintf('Es wurden %d Beiträge gefunden', count($posts)));

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                fn (KnPost $knPost): KnItemInterface => $this->knFactory->createKnItem(
                    $knPost,
                    $user
                ),
                $posts
            )
        );

        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
