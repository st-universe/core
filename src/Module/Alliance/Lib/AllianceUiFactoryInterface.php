<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\User;

interface AllianceUiFactoryInterface
{
    public function createManagementListItem(
        Alliance $alliance,
        User $user,
        int $currentUserId,
    ): ManagementListItem;

    public function createAllianceListItem(
        Alliance $alliance
    ): AllianceListItem;

    public function createAllianceMemberWrapper(
        User $user,
        Alliance $alliance
    ): AllianceMemberWrapper;

    public function createAllianceRelationWrapper(
        Alliance $alliance,
        AllianceRelation $allianceRelation
    ): AllianceRelationWrapper;
}
