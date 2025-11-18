<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ClaimQuestReward;

use Override;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Communication\View\ShowQuest\ShowQuest;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class ClaimQuestReward implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CLAIM_QUEST_REWARD';

    public function __construct(
        private ClaimQuestRewardRequestInterface $claimQuestRewardRequest,
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository,
        private ShipCreatorInterface $shipCreator,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowQuest::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $questId = $this->claimQuestRewardRequest->getQuestId();

        $quest = $this->npcQuestRepository->find($questId);
        if ($quest === null) {
            $game->getInfo()->addInformation('Quest nicht gefunden');
            return;
        }

        $questUser = $this->npcQuestUserRepository->findOneBy([
            'quest_id' => $questId,
            'user_id' => $user->getId()
        ]);

        if ($questUser === null) {
            $game->getInfo()->addInformation('Du bist kein Teilnehmer dieser Quest');
            return;
        }

        if ($questUser->getMode() !== QuestUserModeEnum::ACTIVE_MEMBER) {
            $game->getInfo()->addInformation('Du bist kein aktives Mitglied dieser Quest');
            return;
        }

        if ($quest->getEnd() === null) {
            $game->getInfo()->addInformation('Die Quest ist noch nicht beendet');
            return;
        }

        if ($questUser->isRewardReceived()) {
            $game->getInfo()->addInformation('Du hast die Belohnung bereits erhalten');
            return;
        }

        $hasPhysicalRewards = $quest->getCommodityReward() !== null || $quest->getSpacecrafts() !== null;

        if ($hasPhysicalRewards) {
            $colonyId = $this->claimQuestRewardRequest->getColonyId();

            if ($colonyId === 0) {
                $game->getInfo()->addInformation('Keine Kolonie ausgewählt');
                return;
            }

            $colony = $this->colonyRepository->find($colonyId);

            if ($colony === null) {
                $game->getInfo()->addInformation('Kolonie nicht gefunden');
                return;
            }

            if ($colony->getUserId() !== $user->getId()) {
                $game->getInfo()->addInformation('Die Kolonie gehört dir nicht');
                return;
            }

            $this->distributeRewards($quest, $colony, $user->getId());
        } else {
            $this->distributePrestigeOnly($quest, $user->getId());
        }

        $questUser->setRewardReceived(true);
        $this->npcQuestUserRepository->save($questUser);

        $game->getInfo()->addInformation('Belohnung erfolgreich erhalten!');
    }

    private function distributePrestigeOnly(NPCQuest $quest, int $userId): void
    {
        if ($quest->getPrestige() !== null && $quest->getPrestige() > 0) {
            $questUser = $this->npcQuestUserRepository->findOneBy(['user_id' => $userId, 'quest_id' => $quest->getId()]);
            if ($questUser !== null) {
                $user = $questUser->getUser();
                if ($user !== null) {
                    $this->createPrestigeLog->createLog(
                        $quest->getPrestige(),
                        sprintf('%d Prestige erhalten für Quest "%s"', $quest->getPrestige(), $quest->getTitle()),
                        $user,
                        time()
                    );
                }
            }
        }
    }

    private function distributeRewards(NPCQuest $quest, Colony $colony, int $userId): void
    {
        if ($quest->getPrestige() !== null && $quest->getPrestige() > 0) {
            $questUser = $this->npcQuestUserRepository->findOneBy(['user_id' => $userId, 'quest_id' => $quest->getId()]);
            if ($questUser !== null) {
                $user = $questUser->getUser();
                if ($user !== null) {
                    $this->createPrestigeLog->createLog(
                        $quest->getPrestige(),
                        sprintf('%d Prestige erhalten für Quest "%s"', $quest->getPrestige(), $quest->getTitle()),
                        $user,
                        time()
                    );
                }
            }
        }

        if ($quest->getCommodityReward() !== null) {
            foreach ($quest->getCommodityReward() as $commodityId => $amount) {
                $commodity = $this->commodityRepository->find($commodityId);
                if ($commodity !== null) {
                    $this->storageManager->upperStorage(
                        $colony,
                        $commodity,
                        $amount
                    );
                }
            }
        }

        if ($quest->getSpacecrafts() !== null) {
            $location = $colony->getLocation();

            foreach ($quest->getSpacecrafts() as $buildplanId => $amount) {
                $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);

                if ($buildplan !== null) {
                    for ($i = 0; $i < $amount; $i++) {
                        $this->shipCreator
                            ->createBy($userId, $buildplan->getRump()->getId(), $buildplan->getId())
                            ->setLocation($location)
                            ->maxOutSystems()
                            ->finishConfiguration();
                    }
                }
            }
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}