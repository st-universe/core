<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

interface AllianceActionManagerInterface
{
    public function setJobForUser(Alliance $alliance, User $user, int $jobTypeId): void;

    public function delete(Alliance $alliance, bool $sendMessage = true): void;

    public function mayEdit(Alliance $alliance, User $user): bool;

    public function mayManageForeignRelations(Alliance $alliance, User $user): bool;

    public function sendMessage(int $allianceId, string $text): void;

    public function mayEditFactionMode(Alliance $alliance, int $factionId): bool;
}
