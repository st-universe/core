<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;


/**
 * Creates several UI related item/wrapper classes
 */
final class AllianceUiFactory implements AllianceUiFactoryInterface
{
    private AllianceJobRepositoryInterface $allianceJobRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;


    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->allianceJobRepository = $allianceJobRepository;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function createManagementListItem(
        AllianceInterface $alliance,
        UserInterface $user,
        int $currentUserId
    ): ManagementListItem {
        return new ManagementListItem(
            $this->allianceJobRepository,
            $this->shipRumpRepository,
            $alliance,
            $user,
            $currentUserId
        );
    }

    public function createAllianceListItem(
        AllianceInterface $alliance
    ): AllianceListItem {
        return new AllianceListItem(
            $alliance
        );
    }

    public function createAllianceMemberWrapper(
        UserInterface $user,
        AllianceInterface $alliance
    ): AllianceMemberWrapper {
        return new AllianceMemberWrapper(
            $user,
            $alliance
        );
    }

    public function createAllianceRelationWrapper(
        AllianceInterface $alliance,
        AllianceRelationInterface $allianceRelation
    ): AllianceRelationWrapper {
        return new AllianceRelationWrapper(
            $alliance,
            $allianceRelation
        );
    }
}
