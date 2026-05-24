<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class CrewLimitationsTest extends StuTestCase
{
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;

    private MockInterface&CrewRepositoryInterface $crewRepository;

    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;

    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;

    private MockInterface&AlertReactionFacadeInterface $alertReactionFacade;

    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;

    private CrewLimitations $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->alertReactionFacade = $this->mock(AlertReactionFacadeInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);

        $this->subject = new CrewLimitations(
            $this->privateMessageSender,
            $this->spacecraftRepository,
            $this->mock(UserRepositoryInterface::class),
            $this->crewRepository,
            $this->crewAssignmentRepository,
            $this->spacecraftSystemManager,
            $this->alertReactionFacade,
            $this->spacecraftWrapperFactory,
            $this->mock(CrewLimitCalculatorInterface::class),
            $this->mock(EntityManagerInterface::class)
        );
    }

    public function testLetShipAssignmentsQuitExcludesAlreadyWipedShips(): void
    {
        $userId = 42;
        $spacecraftId = 99;

        $spacecraft = $this->mock(Spacecraft::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);

        $this->spacecraftRepository->shouldReceive('getRandomSpacecraftWithCrewByUser')
            ->with($userId, [])
            ->once()
            ->ordered()
            ->andReturn($spacecraft);
        $this->spacecraftRepository->shouldReceive('getRandomSpacecraftWithCrewByUser')
            ->with($userId, [$spacecraftId])
            ->once()
            ->ordered()
            ->andReturn(null);

        $spacecraft->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($spacecraftId);
        $spacecraft->shouldReceive('getWarpDriveState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $spacecraft->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $spacecraft->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $spacecraft->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection());
        $spacecraft->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('USS Test');
        $spacecraft->shouldReceive('isStation')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($spacecraft)
            ->once()
            ->andReturn($wrapper);
        $this->spacecraftSystemManager->shouldReceive('deactivateAll')
            ->with($wrapper)
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->with(
                UserConstants::USER_NOONE,
                $userId,
                Mockery::type('string'),
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            )
            ->once();

        $result = $this->getMethod($this->subject, 'letShipAssignmentsQuit')
            ->invokeArgs($this->subject, [$userId, 1]);

        static::assertSame(0, $result);
    }
}
