<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\UserInterface;

interface AllianceUiFactoryInterface
{
    public function createManagementListItem(
        AllianceInterface $alliance,
        UserInterface $user,
        int $currentUserId
    ): ManagementListItem;

    public function createAllianceListItem(
        AllianceInterface $alliance
    ): AllianceListItem;

    public function createAllianceMemberWrapper(
        UserInterface $user,
        AllianceInterface $alliance
    ): AllianceMemberWrapper;

    public function createAllianceRelationWrapper(
        AllianceInterface $alliance,
        AllianceRelationInterface $allianceRelation
    ): AllianceRelationWrapper;
}
