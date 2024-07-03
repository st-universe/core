<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Override;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
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
    public function __construct(private AllianceJobRepositoryInterface $allianceJobRepository, private ShipRumpRepositoryInterface $shipRumpRepository, private CrewCountRetrieverInterface $crewCountRetriever, private CrewLimitCalculatorInterface $crewLimitCalculator)
    {
    }

    #[Override]
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
            $currentUserId,
            $this->crewLimitCalculator,
            $this->crewCountRetriever
        );
    }

    #[Override]
    public function createAllianceListItem(
        AllianceInterface $alliance
    ): AllianceListItem {
        return new AllianceListItem(
            $alliance
        );
    }

    #[Override]
    public function createAllianceMemberWrapper(
        UserInterface $user,
        AllianceInterface $alliance
    ): AllianceMemberWrapper {
        return new AllianceMemberWrapper(
            $user,
            $alliance
        );
    }

    #[Override]
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
