<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\ColonyLimitCalculatorInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Module\Spacecraft\Lib\EmergencyWrapper;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class MaindeskProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private readonly HistoryRepositoryInterface $historyRepository,
        private readonly AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        private readonly UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        private readonly KnPostRepositoryInterface $knPostRepository,
        private readonly ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private readonly ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly KnFactoryInterface $knFactory,
        private readonly ColonyLimitCalculatorInterface $colonyLimitCalculator,
        private readonly PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private readonly CrewLimitCalculatorInterface $crewLimitCalculator,
        private readonly CrewCountRetrieverInterface $crewCountRetriever,
        private readonly NPCQuestRepositoryInterface $npcQuestRepository,
        private readonly NPCQuestUserRepositoryInterface $npcQuestUserRepository
    ) {}

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            $user->getState() === UserStateEnum::UNCOLONIZED
        );

        $game->setTemplateVar(
            'DISPLAY_COLONIZATION_SHIP_DIALOGUE',
            $user->getState() === UserStateEnum::COLONIZATION_SHIP
        );

        $newAmount = $this->knPostRepository->getAmountSince($user->getKnMark());

        $game->setTemplateVar(
            'NEW_KN_POSTING_COUNT',
            $newAmount
        );
        $newKnPostings = $this->knPostRepository->getNewerThenMark($user->getKnMark());
        if ($newKnPostings !== []) {
            $game->setTemplateVar('MARKED_KN_ID', $newKnPostings[0]->getId());
        }
        $game->setTemplateVar(
            'NEW_KN_POSTINGS',
            array_map(
                function (KnPost $knPost) use ($user, $newAmount): KnItemInterface {
                    $newAmount--;
                    $knItem = $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                    $knItem->setMark(((int)floor($newAmount / GameEnum::KN_PER_SITE)) * 6);
                    return $knItem;
                },
                $newKnPostings
            )
        );
        $game->setTemplateVar(
            'RECENT_PROFILE_VISITORS',
            $this->userProfileVisitorRepository->getRecent($userId)
        );
        $game->setTemplateVar(
            'RANDOM_ONLINE_USER',
            $this->userRepository->getOrderedByLastaction(35, $userId, time() - GameEnum::USER_ONLINE_PERIOD)
        );
        $game->setTemplateVar(
            'SHIP_BUILD_PROGRESS',
            [...$this->colonyShipQueueRepository->getByUserAndMode($userId, 1), ...$this->shipyardShipQueueRepository->getByUser($userId)]
        );
        $game->setTemplateVar(
            'SHIP_RETROFIT_PROGRESS',
            [...$this->colonyShipQueueRepository->getByUserAndMode($userId, 2)]
        );

        $alliance = $user->getAlliance();
        if ($alliance !== null) {
            $game->setTemplateVar('ALLIANCE', true);

            $game->setTemplateVar(
                'RECENT_ALLIANCE_BOARD_TOPICS',
                $this->allianceBoardTopicRepository->getRecentByAlliance($alliance->getId())
            );
        }

        if ($this->userSettingsProvider->isShowPirateHistoryEntrys($user)) {
            $game->setTemplateVar('RECENT_HISTORY', $this->historyRepository->getRecent());
        } else {
            $game->setTemplateVar('RECENT_HISTORY', $this->historyRepository->getRecentWithoutPirate());
        }

        //emergencies
        $this->setPotentialEmergencies($game);

        //planet
        $game->setTemplateVar('PLANET_LIMIT', $this->colonyLimitCalculator->getColonyLimitWithType($user, ColonyTypeEnum::PLANET));
        $game->setTemplateVar('PLANET_COUNT', $this->colonyLimitCalculator->getColonyCountWithType($user, ColonyTypeEnum::PLANET));

        //moon
        $game->setTemplateVar('MOON_LIMIT', $this->colonyLimitCalculator->getColonyLimitWithType($user, ColonyTypeEnum::MOON));
        $game->setTemplateVar('MOON_COUNT', $this->colonyLimitCalculator->getColonyCountWithType($user, ColonyTypeEnum::MOON));

        //asteroid
        $game->setTemplateVar('ASTEROID_LIMIT', $this->colonyLimitCalculator->getColonyLimitWithType($user, ColonyTypeEnum::ASTEROID));
        $game->setTemplateVar('ASTEROID_COUNT', $this->colonyLimitCalculator->getColonyCountWithType($user, ColonyTypeEnum::ASTEROID));

        $game->setTemplateVar(
            'CREW_LIMIT',
            $this->crewLimitCalculator->getGlobalCrewLimit($user)
        );

        // crew count
        $game->setTemplateVar(
            'CREW_COUNT_SHIPS',
            $this->crewCountRetriever->getAssignedToShipsCount($user)
        );

        $this->setActiveQuests($game);
    }

    private function setPotentialEmergencies(GameControllerInterface $game): void
    {
        $emergencies = $this->spacecraftEmergencyRepository->getActive();

        if ($emergencies === []) {
            return;
        }

        $emergencyWrappers = [];

        foreach ($emergencies as $emergency) {
            $emergencyWrappers[] = new EmergencyWrapper($this->playerRelationDeterminator, $emergency, $game->getUser());
        }

        $game->setTemplateVar('EMERGENCYWRAPPERS', $emergencyWrappers);
    }

    private function setActiveQuests(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userFactionId = $user->getFactionId();

        $userQuestIds = [];
        $userQuests = $this->npcQuestUserRepository->findBy(['user_id' => $user->getId()]);
        foreach ($userQuests as $userQuest) {
            $userQuestIds[] = $userQuest->getQuestId();
        }

        $allActiveQuests = $this->npcQuestRepository->getActiveQuests();
        $visibleQuests = [];

        foreach ($allActiveQuests as $quest) {
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

            if ($canSeeQuest) {
                $visibleQuests[] = $quest;
            }
        }

        $recentQuests = array_slice($visibleQuests, 0, 3);

        $game->setTemplateVar('ACTIVE_QUESTS', $recentQuests);
        $game->setTemplateVar('ACTIVE_QUEST_COUNT', count($visibleQuests));
    }
}