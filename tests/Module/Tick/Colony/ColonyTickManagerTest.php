<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Exception;
use Mockery\MockInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\StuTestCase;
use Ubench;

class ColonyTickManagerTest extends StuTestCase
{
    /** @var MockInterface&ColonyTickInterface */
    private ColonyTickInterface $colonyTick;

    /** @var MockInterface&CrewCreatorInterface */
    private CrewCreatorInterface $crewCreator;

    /** @var MockInterface&CrewTrainingRepositoryInterface */
    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    /** @var MockInterface&ColonyRepositoryInterface */
    private ColonyRepositoryInterface $colonyRepository;

    /** @var MockInterface&PrivateMessageSenderInterface */
    private PrivateMessageSenderInterface $privateMessageSender;

    /** @var MockInterface&CrewCountRetrieverInterface */
    private CrewCountRetrieverInterface $crewCountRetriever;

    /** @var MockInterface&LockManagerInterface */
    private LockManagerInterface $lockManager;

    /** @var ColonyFunctionManagerInterface&MockInterface */
    private ColonyFunctionManagerInterface $colonyFunctionManager;

    /** @var MockInterface&CrewLimitCalculatorInterface */
    private MockInterface $crewLimitCalculator;

    /** @var MockInterface&ColonyLibFactoryInterface */
    private MockInterface $colonyLibFactory;

    /** @var MockInterface&Ubench */
    private MockInterface $benchmark;

    private ColonyTickManagerInterface $subject;

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
            $this->initLoggerUtil(),
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
