<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Mockery\MockInterface;
use Stu\Module\Alliance\View\Management\ManagementListItem;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\StuTestCase;

class AllianceUiFactoryTest extends StuTestCase
{
    /** @var MockInterface&AllianceJobRepositoryInterface */
    private MockInterface $allianceJobRepository;

    /** @var MockInterface&ShipRumpRepositoryInterface */
    private MockInterface $shipRumpRepository;

    private AllianceUiFactory $subject;

    protected function setUp(): void
    {
        $this->allianceJobRepository = $this->mock(AllianceJobRepositoryInterface::class);
        $this->shipRumpRepository = $this->mock(ShipRumpRepositoryInterface::class);

        $this->subject = new AllianceUiFactory(
            $this->allianceJobRepository,
            $this->shipRumpRepository
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
