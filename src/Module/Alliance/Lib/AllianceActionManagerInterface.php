<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;

interface AllianceActionManagerInterface
{
    public function assignUserToJob(User $user, AllianceJob $job): void;

    public function delete(Alliance $alliance, bool $sendMessage = true): void;

    public function sendMessage(int $allianceId, string $text): void;

    public function mayEditFactionMode(Alliance $alliance, int $factionId): bool;
}
