<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Override;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

/**
 * Creates several UI related item/wrapper classes
 */
final class AllianceUiFactory implements AllianceUiFactoryInterface
{
    public function __construct(
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private CrewCountRetrieverInterface $crewCountRetriever,
        private CrewLimitCalculatorInterface $crewLimitCalculator,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[Override]
    public function createManagementListItem(
        Alliance $alliance,
        User $user,
        int $currentUserId
    ): ManagementListItem {
        return new ManagementListItem(
            $this->spacecraftRumpRepository,
            $alliance,
            $user,
            $currentUserId,
            $this->crewLimitCalculator,
            $this->crewCountRetriever,
            $this->allianceJobManager
        );
    }

    #[Override]
    public function createAllianceListItem(
        Alliance $alliance
    ): AllianceListItem {
        return new AllianceListItem(
            $alliance
        );
    }

    #[Override]
    public function createAllianceMemberWrapper(
        User $user,
        Alliance $alliance
    ): AllianceMemberWrapper {
        return new AllianceMemberWrapper(
            $user,
            $alliance,
            $this->allianceJobManager
        );
    }

    #[Override]
    public function createAllianceRelationWrapper(
        Alliance $alliance,
        AllianceRelation $allianceRelation
    ): AllianceRelationWrapper {
        return new AllianceRelationWrapper(
            $alliance,
            $allianceRelation
        );
    }
}
