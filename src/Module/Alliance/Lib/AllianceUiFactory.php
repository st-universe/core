<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;


/**
 * Creates several UI related item/wrapper classes
 */
final class AllianceUiFactory implements AllianceUiFactoryInterface
{
    private AllianceJobRepositoryInterface $allianceJobRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private CrewCountRetrieverInterface $crewCountRetriever;

    private CrewLimitCalculatorInterface $crewLimitCalculator;


    public function __construct(
        AllianceJobRepositoryInterface $allianceJobRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        CrewCountRetrieverInterface $crewCountRetriever,
        CrewLimitCalculatorInterface $crewLimitCalculator

    ) {
        $this->allianceJobRepository = $allianceJobRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->crewCountRetriever = $crewCountRetriever;
        $this->crewLimitCalculator = $crewLimitCalculator;
    }

    public function createManagementListItem(
        AllianceInterface $alliance,
        UserInterface $user,
        int $currentUserId,
        $crewCountRetriever,
        $crewLimitCalculator
    ): ManagementListItem {
        return new ManagementListItem(
            $this->allianceJobRepository,
            $this->shipRumpRepository,
            $alliance,
            $user,
            $currentUserId,
            $crewCountRetriever,
            $crewLimitCalculator

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
