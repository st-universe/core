<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Repository\AwardRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\UserAwardRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class GiveNpcAward implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_GIVE_NPC_AWARD';

    public function __construct(
        private AwardRepositoryInterface $awardRepository,
        private UserRepositoryInterface $userRepository,
        private UserAwardRepositoryInterface $userAwardRepository,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $currentUser = $game->getUser();

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_(
                '[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'
            ));
            return;
        }

        $awardId = request::postInt('assign_award_id');
        if ($awardId === 0) {
            $game->getInfo()->addInformation('Es wurde keine Award-ID angegeben');
            return;
        }

        $award = $this->awardRepository->find($awardId);
        if ($award === null) {
            $game->getInfo()->addInformation('Der Award existiert nicht');
            return;
        }

        if ($currentUser->isNpc() && $award->getIsNpc() !== true) {
            $game->getInfo()->addInformation('NPCs können hier nur NPC-Awards vergeben');
            return;
        }

        $userIdsInput = trim((string) request::postString('assign_user_ids'));
        if ($userIdsInput === '') {
            $game->getInfo()->addInformation('Es wurde keine User-ID angegeben');
            return;
        }

        if (!preg_match('/^[\d\s,]+$/', $userIdsInput)) {
            $game->getInfo()->addInformation('Die User-IDs dürfen nur Zahlen, Kommas und Leerzeichen enthalten');
            return;
        }

        $reason = trim((string) request::postString('reason'));
        if ($currentUser->isNpc() && $reason === '') {
            $game->getInfo()->addInformation('Grund fehlt');
            return;
        }

        $userIds = array_values(array_unique(array_map(
            'intval',
            array_filter(array_map('trim', explode(',', $userIdsInput)))
        )));
        $userIds = array_values(array_filter($userIds, static fn (int $id): bool => $id > 0));

        if ($userIds === []) {
            $game->getInfo()->addInformation('Es wurden keine gültigen User-IDs gefunden');
            return;
        }

        $awardedUsers = [];
        $missingUserIds = [];

        foreach ($userIds as $userId) {
            $user = $this->userRepository->find($userId);
            if ($user === null) {
                $missingUserIds[] = $userId;
                continue;
            }

            $newCount = $this->assignAwardToUser($award, $user);
            $this->createPrestigeLogForAward($award, $user);

            $awardedUsers[] = sprintf('%s (%d, Count %d)', $user->getName(), $user->getId(), $newCount);
        }

        if ($awardedUsers === []) {
            $game->getInfo()->addInformation('Es wurde kein Award vergeben');
            return;
        }

        $reasonText = $reason !== '' ? $reason : '-';

        if ($currentUser->isNpc()) {
            $this->createEntry(
                sprintf(
                    '%s hat den NPC-Award "%s" (%d) an folgende Spieler vergeben: %s. Grund: %s',
                    $currentUser->getName(),
                    $award->getDescription(),
                    $award->getId(),
                    implode(', ', $awardedUsers),
                    $reasonText
                ),
                $currentUser->getId()
            );
        }

        $message = sprintf(
            'Award %d wurde an %d Spieler vergeben',
            $award->getId(),
            count($awardedUsers)
        );

        if ($missingUserIds !== []) {
            $message .= sprintf('. Nicht gefunden: %s', implode(', ', $missingUserIds));
        }

        $game->getInfo()->addInformation($message);
    }

    private function assignAwardToUser(Award $award, User $user): int
    {
        /** @var UserAward|null $existingUserAward */
        $existingUserAward = $this->userAwardRepository->findOneBy([
            'user_id' => $user->getId(),
            'award_id' => $award->getId()
        ]);

        if ($existingUserAward === null) {
            $newUserAward = $this->userAwardRepository->prototype();
            $newUserAward
                ->setUser($user)
                ->setAward($award)
                ->setCount(1);

            $this->userAwardRepository->save($newUserAward);

            return 1;
        }

        $currentCount = $existingUserAward->getCount() ?? 1;
        $newCount = $currentCount + 1;
        $existingUserAward->setCount($newCount);
        $this->userAwardRepository->save($existingUserAward);

        return $newCount;
    }

    private function createPrestigeLogForAward(Award $award, User $user): void
    {
        if ($award->getPrestige() === 0) {
            return;
        }

        $this->createPrestigeLog->createLog(
            $award->getPrestige(),
            sprintf(
                '%d Prestige erhalten für den Erhalt des Awards "%s"',
                $award->getPrestige(),
                $award->getDescription()
            ),
            $user,
            time()
        );
    }

    private function createEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
