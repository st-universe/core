<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;

interface AllianceActionManagerInterface
{
    public function setJobForUser(int $allianceId, int $userId, int $jobTypeId): void;

    public function delete(AllianceInterface $alliance, bool $sendMessage = true): void;

    public function mayEdit(AllianceInterface $alliance, UserInterface $user): bool;

    public function mayManageForeignRelations(AllianceInterface $alliance, UserInterface $user): bool;

    public function sendMessage(int $allianceId, string $text): void;

    public function mayEditFactionMode(AllianceInterface $alliance, int $factionId): bool;
}
