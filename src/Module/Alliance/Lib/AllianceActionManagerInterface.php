<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use AllianceData;

interface AllianceActionManagerInterface
{
    public function setJobForUser(int $allianceId, int $userId, int $jobTypeId): void;

    public function delete(int $allianceId): void;

    public function mayEdit(int $allianceId, int $userId): bool;

    public function mayManageForeignRelations(int $allianceId, int $userId): bool;

    public function sendMessage(int $allianceId, string $text): void;

    public function mayEditFactionMode(AllianceData $alliance, int $factionId): bool;
}