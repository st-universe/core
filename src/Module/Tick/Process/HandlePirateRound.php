<?php

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Component\Map\MapEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\PirateRound;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\PirateRoundRepositoryInterface;
use Stu\Orm\Repository\UserPirateRoundRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserPirateRound;


final class HandlePirateRound implements ProcessTickHandlerInterface
{
    public const array FORBIDDEN_ADMIN_AREAS = [
        MapEnum::ADMIN_REGION_SUPERPOWER_CENTRAL,
        MapEnum::ADMIN_REGION_SUPERPOWER_PERIPHERAL
    ];

    public function __construct(
        private PirateRoundRepositoryInterface $pirateRoundRepository,
        private UserPirateRoundRepositoryInterface $userPirateRoundRepository,
        private UserRepositoryInterface $userRepository,
        private EntryCreatorInterface $entryCreator,
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository,
        private CreatePrestigeLogInterface $createPrestigeLog
    ) {}


    #[Override]
    public function work(): void
    {
        $lastRound = $this->getLastPirateRound();

        if ($lastRound === null) {
            return;
        }

        $endTime = $lastRound->getEndTime();

        if ($endTime !== null && $lastRound->getFactionWinner() == null) {
            $this->handleRecentlyEndedRound($lastRound);
            return;
        }
    }

    private function getLastPirateRound(): ?PirateRound
    {
        $allRounds = $this->pirateRoundRepository->findBy([], ['id' => 'DESC'], 1);

        return empty($allRounds) ? null : $allRounds[0];
    }

    private function handleRecentlyEndedRound(PirateRound $pirateRound): void
    {
        $winningFactionData = $this->getWinningFactionData($pirateRound->getId());

        if ($winningFactionData === null) {
            return;
        }

        $pirateRound->setFactionWinner($winningFactionData['factionId']);
        $this->pirateRoundRepository->save($pirateRound);

        $this->distributePrestigeRewards($pirateRound->getId(), $winningFactionData);


        $this->createHistoryEntry(
            sprintf('Die Siedler %s haben die Kazon in die Flucht geschlagen!', $this->getFactionNameWithArticle($winningFactionData['factionId']))
        );
    }

    /**
     * @return array{factionId: int, factionName: string}|null
     */
    private function getWinningFactionData(int $pirateRoundId): ?array
    {
        $userPirateRounds = $this->userPirateRoundRepository->findByPirateRound($pirateRoundId);

        if (empty($userPirateRounds)) {
            return null;
        }

        $factionPrestige = [];
        $factionData = [];

        foreach ($userPirateRounds as $userPirateRound) {
            $user = $userPirateRound->getUser();
            $faction = $user->getFaction();
            $factionId = $faction->getId();
            $factionName = $faction->getName();
            $prestige = $userPirateRound->getPrestige();

            if (!isset($factionPrestige[$factionId])) {
                $factionPrestige[$factionId] = 0;
                $factionData[$factionId] = $factionName;
            }

            $factionPrestige[$factionId] += $prestige;
        }

        $maxPrestige = max($factionPrestige);
        $winningFactionIds = array_keys($factionPrestige, $maxPrestige);
        $winningFactionId = $winningFactionIds[0];

        return [
            'factionId' => $winningFactionId,
            'factionName' => $factionData[$winningFactionId]
        ];
    }

    private function getFactionNameWithArticle(int $factionId): string
    {
        return match ($factionId) {
            1 => 'der Vereinten Föderation der Planeten',
            2 => 'des Romulanischen Imperiums',
            3 => 'des Klingonischen Reichs',
            4 => 'der Cardassianischen Union',
            5 => 'der Ferengi Allianz',
            default => 'einer unbekannten Fraktion'
        };
    }

    private function createHistoryEntry(string $text): void
    {
        $nooneUser = $this->userRepository->find(UserEnum::USER_NOONE);
        $kazonUser = $this->userRepository->find(UserEnum::USER_NOONE);

        if ($nooneUser === null || $kazonUser === null) {
            return;
        }

        $randomLocation = $this->getRandomMapLocation();

        $this->entryCreator->createEntry(
            HistoryTypeEnum::OTHER,
            $text,
            $nooneUser->getId(),
            $kazonUser->getId(),
            $randomLocation
        );
    }

    private function getRandomMapLocation(): Map
    {
        $defaultLayer = $this->layerRepository->getDefaultLayer();

        do {
            $map = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage($defaultLayer);
        } while (
            in_array($map->getAdminRegionId(), self::FORBIDDEN_ADMIN_AREAS)
            || $map->getFieldType()->hasEffect(FieldTypeEffectEnum::NO_PIRATES)
        );

        return $map;
    }
    /**
     * @param array{factionId: int, factionName: string} $winningFactionData
     */
    private function distributePrestigeRewards(int $pirateRoundId, array $winningFactionData): void
    {
        $userPirateRounds = $this->userPirateRoundRepository->findByPirateRound($pirateRoundId);
        $topThreeUsers = $this->getTopThreeUsers($userPirateRounds);
        $winningFactionTotalPrestige = $this->calculateFactionTotalPrestige($userPirateRounds, $winningFactionData['factionId']);

        $allUsers = $this->userRepository->getNonNpcList();

        foreach ($allUsers as $user) {
            $prestigeReward = $this->calculateUserPrestigeReward(
                $user,
                $winningFactionData,
                $winningFactionTotalPrestige,
                $topThreeUsers
            );

            if ($prestigeReward['amount'] > 0) {
                $this->createPrestigeLog->createLog(
                    $prestigeReward['amount'],
                    $prestigeReward['text'],
                    $user,
                    time()
                );
            }
        }
    }

    /**
     * @param UserPirateRound[] $userPirateRounds
     * @return UserPirateRound[]
     */
    private function getTopThreeUsers(array $userPirateRounds): array
    {
        usort($userPirateRounds, fn($a, $b) => $b->getPrestige() <=> $a->getPrestige());

        return array_slice($userPirateRounds, 0, 3);
    }

    /**
     * @param UserPirateRound[] $userPirateRounds
     */
    private function calculateFactionTotalPrestige(array $userPirateRounds, int $winningFactionId): int
    {
        $total = 0;
        foreach ($userPirateRounds as $userPirateRound) {
            if ($userPirateRound->getUser()->getFactionId() === $winningFactionId) {
                $total += $userPirateRound->getPrestige();
            }
        }
        return $total;
    }

    /**
     * @param array{factionId: int, factionName: string} $winningFactionData
     * @param UserPirateRound[] $topThreeUsers
     * @return array{amount: int, text: string}
     */
    private function calculateUserPrestigeReward(
        User $user,
        array $winningFactionData,
        int $winningFactionTotalPrestige,
        array $topThreeUsers
    ): array {
        $totalPrestige = 0;
        $textParts = [];

        $isWinningFaction = $user->getFactionId() === $winningFactionData['factionId'];
        $userTopThreeEntry = $this->findUserInTopThree($user, $topThreeUsers);
        $allianceTopThreeUsers = $this->getAllianceTopThreeUsers($user, $topThreeUsers);

        if ($isWinningFaction) {
            $totalPrestige += (int)round($winningFactionTotalPrestige * 0.2);
            $textParts[] = 'deine Fraktion';
        }

        if ($userTopThreeEntry !== null) {
            $totalPrestige += (int)round($userTopThreeEntry->getPrestige() * 0.5);
            array_unshift($textParts, 'Du');
        }

        if (!empty($allianceTopThreeUsers)) {
            foreach ($allianceTopThreeUsers as $allianceUser) {
                if ($allianceUser->getUser()->getId() !== $user->getId()) {
                    $totalPrestige += (int)round($allianceUser->getPrestige() * 0.3);
                }
            }
            $textParts[] = 'Siedler deiner Allianz';
        }

        if (empty($textParts)) {
            return ['amount' => 0, 'text' => ''];
        }

        $subject = $this->buildSubjectString($textParts);
        $verb = $this->getCorrectVerb($textParts);

        $text = sprintf(
            '%d Prestige: %s %s die Kazon erfolgreich in die Flucht geschlagen',
            $totalPrestige,
            $subject,
            $verb
        );

        return ['amount' => $totalPrestige, 'text' => $text];
    }

    /**
     * @param string[] $textParts
     */
    private function buildSubjectString(array $textParts): string
    {
        if (count($textParts) === 1) {
            return ucfirst($textParts[0]);
        }

        if (count($textParts) === 2) {
            return ucfirst($textParts[0]) . ' und ' . $textParts[1];
        }

        $lastPart = array_pop($textParts);
        return ucfirst(implode(', ', $textParts)) . ' und ' . $lastPart;
    }

    /**
     * @param string[] $textParts
     */
    private function getCorrectVerb(array $textParts): string
    {
        if (count($textParts) === 1 && $textParts[0] === 'Du') {
            return 'hast';
        }

        if (count($textParts) === 1 && $textParts[0] === 'deine Fraktion') {
            return 'hat';
        }

        if (count($textParts) === 1 && $textParts[0] === 'Siedler deiner Allianz') {
            return 'haben';
        }

        return 'haben';
    }




    /**
     * @param UserPirateRound[] $topThreeUsers
     */
    private function findUserInTopThree(User $user, array $topThreeUsers): ?UserPirateRound
    {
        foreach ($topThreeUsers as $topUser) {
            if ($topUser->getUser()->getId() === $user->getId()) {
                return $topUser;
            }
        }
        return null;
    }

    /**
     * @param UserPirateRound[] $topThreeUsers
     * @return UserPirateRound[]
     */
    private function getAllianceTopThreeUsers(User $user, array $topThreeUsers): array
    {
        $userAlliance = $user->getAlliance();
        if ($userAlliance === null) {
            return [];
        }

        $allianceUsers = [];
        foreach ($topThreeUsers as $topUser) {
            $topUserAlliance = $topUser->getUser()->getAlliance();
            if ($topUserAlliance !== null && $topUserAlliance->getId() === $userAlliance->getId()) {
                $allianceUsers[] = $topUser;
            }
        }

        return $allianceUsers;
    }
}
