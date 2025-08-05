<?php

declare(strict_types=1);

namespace Stu\Component\Database;

use Stu\Orm\Entity\User;

interface AchievementManagerInterface
{
    /** @return array<string> */
    public function getAchievements(): array;

    public function checkDatabaseItem(?int $databaseEntryId, User $user): void;

    public static function reset(): void;
}
