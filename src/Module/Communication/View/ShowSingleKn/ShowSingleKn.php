<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowSingleKn;

use Override;
use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowSingleKn implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SINGLE_KN';

    public function __construct(
        private ShowSingleKnRequestInterface $showSingleKnRequest,
        private KnPostRepositoryInterface $knPostRepository,
        private KnFactoryInterface $knFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $post = $this->knPostRepository->findActiveById($this->showSingleKnRequest->getKnId());

        $game->setPageTitle(_('Kommunikationsnetzwerk'));
        $game->setViewTemplate(ModuleEnum::COMMUNICATION->getTemplate());
        $game->appendNavigationPart('comm.php', _('KommNet'));

        $knPostings = [];
        if ($post !== null) {
            $knPostings[] = $this->knFactory->createKnItem(
                $post,
                $user
            );
        } else {
            $game->getInfo()->addInformation('Dieser Beitrag existiert nicht mehr');
        }

        $game->setTemplateVar('KN_POSTINGS', $knPostings);
        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
