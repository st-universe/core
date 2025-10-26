<?php

declare(strict_types=1);

namespace Stu\Component\Database;

use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

class AchievementManager implements AchievementManagerInterface
{
    /** @var array<string> */
    private static array $achievements = [];

    public function __construct(
        private readonly DatabaseUserRepositoryInterface $databaseUserRepository,
        private readonly CreateDatabaseEntryInterface $createDatabaseEntry
    ) {}

    #[\Override]
    public function getAchievements(): array
    {
        return self::$achievements;
    }

    #[\Override]
    public function checkDatabaseItem(?int $databaseEntryId, User $user): void
    {
        if (
            $databaseEntryId === null
            || $databaseEntryId < 1
        ) {
            return;
        }

        if (!$this->databaseUserRepository->exists($user->getId(), $databaseEntryId)) {
            $entry = $this->createDatabaseEntry->createDatabaseEntryForUser($user, $databaseEntryId);

            if ($entry !== null) {
                self::$achievements[] = sprintf(
                    'Neuer Datenbankeintrag: %s (+%d Punkte)',
                    $entry->getDescription(),
                    $entry->getCategory()->getPoints()
                );
            }
        }
    }

    #[\Override]
    public static function reset(): void
    {
        self::$achievements = [];
    }
}
