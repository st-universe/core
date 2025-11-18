<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowQuestColonySelection;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;

final class ShowQuestColonySelection implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_QUEST_COLONY_SELECTION';

    public function __construct(
        private ShowQuestColonySelectionRequestInterface $showQuestColonySelectionRequest,
        private NPCQuestRepositoryInterface $npcQuestRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $questId = $this->showQuestColonySelectionRequest->getQuestId();

        $game->setPageTitle('Kolonie wählen');
        $game->setMacroInAjaxWindow('html/communication/quest/selectColony.twig');

        if ($questId === null) {
            $game->getInfo()->addInformation('Keine Quest-ID angegeben');
            return;
        }

        $quest = $this->npcQuestRepository->find($questId);
        if ($quest === null) {
            $game->getInfo()->addInformation('Diese Quest existiert nicht');
            return;
        }

        $colonies = $user->getColonies()->toArray();

        $game->setTemplateVar('QUEST_ID', $questId);
        $game->setTemplateVar('COLONIES', $colonies);
    }
}