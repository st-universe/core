<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\StuTestCase;

class AllianceUiFactoryTest extends StuTestCase
{
    private MockInterface&AllianceJobRepositoryInterface $allianceJobRepository;

    private MockInterface&SpacecraftRumpRepositoryInterface $spacecraftRumpRepository;

    private MockInterface&CrewCountRetrieverInterface $crewCountRetriever;

    private MockInterface&CrewLimitCalculatorInterface $crewLimitCalculator;

    private AllianceUiFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->allianceJobRepository = $this->mock(AllianceJobRepositoryInterface::class);
        $this->spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $this->crewLimitCalculator = $this->mock(CrewLimitCalculatorInterface::class);
        $this->crewCountRetriever = $this->mock(CrewCountRetrieverInterface::class);


        $this->subject = new AllianceUiFactory(
            $this->allianceJobRepository,
            $this->spacecraftRumpRepository,
            $this->crewCountRetriever,
            $this->crewLimitCalculator
        );
    }

    public function testCreateManagementListItemReturnsValue(): void
    {
        static::assertInstanceOf(
            ManagementListItem::class,
            $this->subject->createManagementListItem(
                $this->mock(AllianceInterface::class),
                $this->mock(UserInterface::class),
                666
            )
        );
    }

    public function testCreateAllianceListItemReturnsValue(): void
    {
        static::assertInstanceOf(
            AllianceListItem::class,
            $this->subject->createAllianceListItem(
                $this->mock(AllianceInterface::class)
            )
        );
    }

    public function testCreateAllianceMemberWrapperReturnsValue(): void
    {
        static::assertInstanceOf(
            AllianceMemberWrapper::class,
            $this->subject->createAllianceMemberWrapper(
                $this->mock(UserInterface::class),
                $this->mock(AllianceInterface::class)
            )
        );
    }

    public function testCreateAllianceRelationWrapperReturnsValue(): void
    {
        static::assertInstanceOf(
            AllianceRelationWrapper::class,
            $this->subject->createAllianceRelationWrapper(
                $this->mock(AllianceInterface::class),
                $this->mock(AllianceRelationInterface::class)
            )
        );
    }
}
