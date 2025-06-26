<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\User;
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
                $this->mock(Alliance::class),
                $this->mock(User::class),
                666
            )
        );
    }

    public function testCreateAllianceListItemReturnsValue(): void
    {
        static::assertInstanceOf(
            AllianceListItem::class,
            $this->subject->createAllianceListItem(
                $this->mock(Alliance::class)
            )
        );
    }

    public function testCreateAllianceMemberWrapperReturnsValue(): void
    {
        static::assertInstanceOf(
            AllianceMemberWrapper::class,
            $this->subject->createAllianceMemberWrapper(
                $this->mock(User::class),
                $this->mock(Alliance::class)
            )
        );
    }

    public function testCreateAllianceRelationWrapperReturnsValue(): void
    {
        static::assertInstanceOf(
            AllianceRelationWrapper::class,
            $this->subject->createAllianceRelationWrapper(
                $this->mock(Alliance::class),
                $this->mock(AllianceRelation::class)
            )
        );
    }
}
