<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowQuest;

use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\NPCQuestLogRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class ShowQuest implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_QUEST';

    public function __construct(
        private ShowQuestRequestInterface $showQuestRequest,
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestLogRepositoryInterface $npcQuestLogRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private FactionRepositoryInterface $factionRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private CommodityRepositoryInterface $commodityRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userFactionId = $user->getFactionId();
        $questId = $this->showQuestRequest->getQuestId();

        $game->setViewTemplate('html/communication/quest/questDetails.twig');
        $game->setPageTitle(_('Quest'));
        $game->appendNavigationPart('comm.php', _('KommNet'));
        $game->appendNavigationPart('comm.php?SHOW_QUESTLIST=1', _('Quests'));

        if ($questId === null) {
            $game->getInfo()->addInformation('Keine Quest-ID angegeben');
            return;
        }

        $quest = $this->npcQuestRepository->find($questId);
        if ($quest === null) {
            $game->getInfo()->addInformation('Diese Quest existiert nicht');
            return;
        }

        $userQuests = $this->npcQuestUserRepository->findBy(['user_id' => $user->getId()]);
        $userQuestIds = [];
        foreach ($userQuests as $userQuest) {
            $userQuestIds[] = $userQuest->getQuestId();
        }

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
            $game->getInfo()->addInformation('Du hast keine Berechtigung, diese Quest anzusehen');
            return;
        }

        $questLogs = $this->npcQuestLogRepository->getActiveByQuest($quest->getId());

        $userQuestData = null;
        foreach ($userQuests as $userQuest) {
            if ($userQuest->getQuestId() === $quest->getId()) {
                $userQuestData = $userQuest;
                break;
            }
        }

        $currentTime = time();
        $canApply = false;
        $canAcceptInvitation = false;
        $userStatus = null;
        $canClaimReward = false;
        $hasPhysicalRewards = false;

        if ($quest->getEnd() === null && $quest->getApplicationEnd() > $currentTime) {
            if ($userQuestData === null) {
                $canSeeFactions = $quest->getFactions();
                if ($canSeeFactions === null || in_array($userFactionId, $canSeeFactions)) {
                    if ($quest->getApplicantMax() === null) {
                        $canApply = true;
                    } else {
                        $activeMembersCount = count($quest->getQuestUsers()->filter(
                            fn ($questUser) => $questUser->getMode()->value === 1
                        ));
                        if ($activeMembersCount < $quest->getApplicantMax()) {
                            $canApply = true;
                        }
                    }
                }
            } else {
                $userStatus = $userQuestData->getMode();
                if ($userStatus === QuestUserModeEnum::INVITED) {
                    $canAcceptInvitation = true;
                }
            }
        }

        if (
            $userQuestData !== null &&
            $userQuestData->getMode() === QuestUserModeEnum::ACTIVE_MEMBER &&
            $quest->getEnd() !== null &&
            !$userQuestData->isRewardReceived()
        ) {
            $canClaimReward = true;
            $hasPhysicalRewards = $quest->getCommodityReward() !== null || $quest->getSpacecrafts() !== null;
        }

        $game->setPageTitle(sprintf('Quest: %s', $quest->getTitle()));
        $game->appendNavigationPart(
            sprintf(
                'comm.php?%s=1&questid=%d',
                self::VIEW_IDENTIFIER,
                $quest->getId()
            ),
            $quest->getTitle()
        );

        $game->setTemplateVar('QUEST', $quest);
        $game->setTemplateVar('QUEST_LOGS', $questLogs);
        $game->setTemplateVar('IS_PARTICIPANT', $isParticipant);
        $game->setTemplateVar('USER_QUEST_IDS', $userQuestIds);
        $game->setTemplateVar('PLAYABLE_FACTIONS', $this->factionRepository->getByChooseable(true));
        $game->setTemplateVar('USER_QUEST_DATA', $userQuestData);
        $game->setTemplateVar('CAN_APPLY', $canApply);
        $game->setTemplateVar('CAN_ACCEPT_INVITATION', $canAcceptInvitation);
        $game->setTemplateVar('USER_STATUS', $userStatus);
        $game->setTemplateVar('CURRENT_TIME', $currentTime);
        $game->setTemplateVar('BUILDPLANS', $this->loadBuildplans($quest));
        $game->setTemplateVar('COMMODITIES', $this->loadCommodities($quest));
        $game->setTemplateVar('CAN_CLAIM_REWARD', $canClaimReward);
        $game->setTemplateVar('HAS_PHYSICAL_REWARDS', $hasPhysicalRewards);
    }

    /**
     * @param NPCQuest $quest
     * @return array<int, object>
     */
    private function loadBuildplans(NPCQuest $quest): array
    {
        $spacecrafts = $quest->getSpacecrafts();
        if (!$spacecrafts) {
            return [];
        }

        $buildplans = [];
        foreach ($spacecrafts as $buildplanId => $amount) {
            $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);
            if ($buildplan !== null) {
                $buildplans[$buildplanId] = $buildplan;
            }
        }

        return $buildplans;
    }

    /**
     * @param NPCQuest $quest
     * @return array<int, object>
     */
    private function loadCommodities(NPCQuest $quest): array
    {
        $commodityReward = $quest->getCommodityReward();
        if (!$commodityReward) {
            return [];
        }

        $commodities = [];
        foreach ($commodityReward as $commodityId => $amount) {
            $commodity = $this->commodityRepository->find($commodityId);
            if ($commodity !== null) {
                $commodities[$commodityId] = $commodity;
            }
        }

        return $commodities;
    }
}
