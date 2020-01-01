<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;

interface AllianceActionManagerInterface
{
    public function setJobForUser(int $allianceId, int $userId, int $jobTypeId): void;

    public function delete(int $allianceId, bool $sendMessage = true): void;

    public function mayEdit(int $allianceId, int $userId): bool;

    public function mayManageForeignRelations(int $allianceId, int $userId): bool;

    public function sendMessage(int $allianceId, string $text): void;

    public function mayEditFactionMode(AllianceInterface $alliance, int $factionId): bool;
}
