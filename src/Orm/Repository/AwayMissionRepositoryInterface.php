<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\AwayMissionDialogue;
use Stu\Orm\Entity\AwayMissionField;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\User;

interface AwayMissionRepositoryInterface
{
    public function getByColony(Colony $colony): ?AwayMission;

    /** @return list<AwayMissionField> */
    public function getFields(AwayMission $mission): array;

    /** @return array<int, array<int, AwayMissionDialogue>> */
    public function getDialogue(AwayMission $mission, int $factionId): array;

    public function getProgress(AwayMission $mission, User $user, bool $forUpdate = false): ?AwayMissionProgress;

    public function initializeProgress(AwayMission $mission, User $user): AwayMissionProgress;

    public function saveProgress(AwayMissionProgress $progress): void;

    public function deleteByUser(User $user): void;

    public function deleteByQuestOwner(User $user): void;

    public function deleteByColony(Colony $colony): void;

    public function deleteByQuest(NPCQuest $quest): void;
}
