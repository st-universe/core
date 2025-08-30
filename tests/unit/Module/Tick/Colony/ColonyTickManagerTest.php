<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Exception;
use Mockery\MockInterface;
use Override;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\StuTestCase;
use Ubench;

class ColonyTickManagerTest extends StuTestCase
{
    private MockInterface&ColonyTickInterface $colonyTick;
    private MockInterface&CrewCreatorInterface $crewCreator;
    private MockInterface&CrewTrainingRepositoryInterface $crewTrainingRepository;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&CrewCountRetrieverInterface $crewCountRetriever;
    private MockInterface&LockManagerInterface $lockManager;
    private ColonyFunctionManagerInterface&MockInterface $colonyFunctionManager;
    private MockInterface&CrewLimitCalculatorInterface $crewLimitCalculator;
    private MockInterface&ColonyLibFactoryInterface $colonyLibFactory;
    private MockInterface&Ubench $benchmark;

    private ColonyTickManagerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->colonyTick = $this->mock(ColonyTickInterface::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->crewTrainingRepository = $this->mock(CrewTrainingRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->crewCountRetriever = $this->mock(CrewCountRetrieverInterface::class);
        $this->lockManager = $this->mock(LockManagerInterface::class);
        $this->colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $this->crewLimitCalculator = $this->mock(CrewLimitCalculatorInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->benchmark = $this->mock(Ubench::class);

        $this->subject = new ColonyTickManager(
            $this->colonyTick,
            $this->crewCreator,
            $this->crewTrainingRepository,
            $this->colonyRepository,
            $this->privateMessageSender,
            $this->crewCountRetriever,
            $this->colonyFunctionManager,
            $this->crewLimitCalculator,
            $this->colonyLibFactory,
            $this->lockManager,
            $this->benchmark
        );
    }

    public function testWorkExpectLockReleaseWhenError(): void
    {
        $groupId = 1;

        static::expectException(Exception::class);

        $this->colonyRepository->shouldReceive('getByBatchGroup')
            ->with($groupId, 1)
            ->once()
            ->andThrow(new Exception(''));

        $this->lockManager->shouldReceive('setLock')
            ->with($groupId, LockTypeEnum::COLONY_GROUP)
            ->once();
        $this->lockManager->shouldReceive('clearLock')
            ->with($groupId, LockTypeEnum::COLONY_GROUP)
            ->once();

        $this->subject->work($groupId, 1);
    }
}
