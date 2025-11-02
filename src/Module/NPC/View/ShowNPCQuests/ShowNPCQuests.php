<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowNPCQuests;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class ShowNPCQuests implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NPC_QUESTS';

    public function __construct(
        private FactionRepositoryInterface $factionRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                '/npc/?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('NPC Quests')
        );

        $game->setTemplateFile('html/npc/npcquests.twig');
        $game->setPageTitle(_('NPC Quests'));

        $userId = $game->getUser()->getId();

        $myActiveQuests = $this->npcQuestRepository->getActiveQuestsByUser($userId);
        $myFinishedQuests = $this->npcQuestRepository->getFinishedQuestsByUser($userId);

        $game->setTemplateVar('PLAYABLE_FACTIONS', $this->factionRepository->getByChooseable(true));
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $this->commodityRepository->getTradeableNPC());
        $game->setTemplateVar('MY_ACTIVE_QUESTS', $myActiveQuests);
        $game->setTemplateVar('MY_FINISHED_QUESTS', $myFinishedQuests);
        $game->setTemplateVar('BUILDPLANS', $this->loadBuildplans($myActiveQuests, $myFinishedQuests));
    }

    /**
     * @param array<NPCQuest> $activeQuests
     * @param array<NPCQuest> $finishedQuests
     * @return array<int, object>
     */
    private function loadBuildplans(array $activeQuests, array $finishedQuests): array
    {
        $buildplanIds = [];
        $allQuests = array_merge($activeQuests, $finishedQuests);

        foreach ($allQuests as $quest) {
            $spacecrafts = $quest->getSpacecrafts();
            if ($spacecrafts) {
                foreach ($spacecrafts as $buildplanId => $amount) {
                    $buildplanIds[] = $buildplanId;
                }
            }
        }

        if (empty($buildplanIds)) {
            return [];
        }

        $buildplans = [];
        foreach (array_unique($buildplanIds) as $buildplanId) {
            $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);
            if ($buildplan !== null) {
                $buildplans[$buildplanId] = $buildplan;
            }
        }

        return $buildplans;
    }
}
