<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowQuestList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Commodity;

final class ShowQuestList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_QUESTLIST';

    public function __construct(
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private CommodityRepositoryInterface $commodityRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userFactionId = $user->getFactionId();

        $activeQuests = [];
        $participatedQuests = [];
        $otherEndedQuests = [];

        $userQuestIds = [];
        $userQuests = $this->npcQuestUserRepository->findBy(['user_id' => $user->getId()]);
        foreach ($userQuests as $userQuest) {
            $userQuestIds[] = $userQuest->getQuestId();
        }

        $allQuests = $this->npcQuestRepository->findAll();

        foreach ($allQuests as $quest) {
            $isParticipant = in_array($quest->getId(), $userQuestIds);

            $canSeeFactions = $quest->getFactions();
            $secretFactions = $quest->getSecret();

            $canSeeQuest = false;
            if ($canSeeFactions === null || in_array($userFactionId, $canSeeFactions)) {
                $canSeeQuest = true;
            }

            if ($secretFactions !== null && !in_array($userFactionId, $secretFactions)) {
                $canSeeQuest = false;
            }

            if ($isParticipant) {
                $canSeeQuest = true;
            }

            if (!$canSeeQuest) {
                continue;
            }

            if ($quest->getEnd() === null) {
                $activeQuests[] = $quest;
            } else {
                if ($isParticipant) {
                    $participatedQuests[] = $quest;
                } else {
                    $otherEndedQuests[] = $quest;
                }
            }
        }

        $game->setViewTemplate('html/communication/quest/quests.twig');
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart(sprintf('comm.php?%s=1', self::VIEW_IDENTIFIER), _('Quests'));
        $game->setPageTitle(_('Quests'));
        $game->setTemplateVar('ACTIVE_QUESTS', $activeQuests);
        $game->setTemplateVar('PARTICIPATED_QUESTS', $participatedQuests);
        $game->setTemplateVar('OTHER_ENDED_QUESTS', $otherEndedQuests);
        $game->setTemplateVar('USER_QUEST_IDS', $userQuestIds);
        $game->setTemplateVar('BUILDPLANS', $this->loadAllBuildplans($allQuests));
        $game->setTemplateVar('COMMODITIES', $this->loadAllCommodities($allQuests));
    }

    /**
     * @param array<int, NPCQuest> $quests
     * @return array<int, SpacecraftBuildplan>
     */
    private function loadAllBuildplans(array $quests): array
    {
        $buildplans = [];
        foreach ($quests as $quest) {
            $spacecrafts = $quest->getSpacecrafts();
            if ($spacecrafts) {
                foreach ($spacecrafts as $buildplanId => $amount) {
                    if (!isset($buildplans[$buildplanId])) {
                        $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);
                        if ($buildplan !== null) {
                            $buildplans[$buildplanId] = $buildplan;
                        }
                    }
                }
            }
        }
        return $buildplans;
    }

    /**
     * @param array<int, NPCQuest> $quests
     * @return array<int, Commodity>
     */
    private function loadAllCommodities(array $quests): array
    {
        $commodities = [];
        foreach ($quests as $quest) {
            $commodityReward = $quest->getCommodityReward();
            if ($commodityReward) {
                foreach ($commodityReward as $commodityId => $amount) {
                    if (!isset($commodities[$commodityId])) {
                        $commodity = $this->commodityRepository->find($commodityId);
                        if ($commodity !== null) {
                            $commodities[$commodityId] = $commodity;
                        }
                    }
                }
            }
        }
        return $commodities;
    }
}
