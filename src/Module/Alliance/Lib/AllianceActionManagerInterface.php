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

    public function mayEdit(Alliance $alliance, User $user): bool;

    public function mayManageForeignRelations(Alliance $alliance, User $user): bool;

    public function mayManageAlliance(Alliance $alliance, User $user): bool;

    public function mayCreateAgreements(Alliance $alliance, User $user): bool;

    public function mayEditDiplomaticDocuments(Alliance $alliance, User $user): bool;

    public function mayManageApplications(Alliance $alliance, User $user): bool;

    public function mayManageJobs(Alliance $alliance, User $user): bool;

    public function mayViewColonies(Alliance $alliance, User $user): bool;

    public function mayViewMemberData(Alliance $alliance, User $user): bool;

    public function mayViewShips(Alliance $alliance, User $user): bool;

    public function mayViewAllianceStorage(Alliance $alliance, User $user): bool;

    public function mayViewAllianceHistory(Alliance $alliance, User $user): bool;

    public function sendMessage(int $allianceId, string $text): void;

    public function mayEditFactionMode(Alliance $alliance, int $factionId): bool;
}
