<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use Mockery\MockInterface;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Control\GameStateInterface;
use Stu\Orm\Entity\GameConfig;
use Stu\StuTestCase;

class GameConfigRepositoryTest extends StuTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;

    private MockInterface&ClassMetadata $classMetaData;

    private GameConfigRepository $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->classMetaData = $this->mock(ClassMetadata::class);

        $this->classMetaData->name = GameConfig::class;

        $this->subject = new GameConfigRepository(
            $this->entityManager,
            $this->classMetaData
        );
    }

    public function testSaveSaves(): void
    {
        $item = $this->mock(GameConfig::class);

        $this->entityManager->shouldReceive('persist')
            ->with($item)
            ->once();
        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->save($item);
    }

    public function testGetByOptionsReturnsItem(): void
    {
        $optionId = 666;

        $item = $this->mock(GameConfig::class);
        $persister = $this->mock(EntityPersister::class);
        $unitOfWork = $this->mock(UnitOfWork::class);

        $this->entityManager->shouldReceive('getUnitOfWork')
            ->withNoArgs()
            ->once()
            ->andReturn($unitOfWork);

        $unitOfWork->shouldReceive('getEntityPersister')
            ->with(GameConfig::class)
            ->once()
            ->andReturn($persister);

        $persister->shouldReceive('load')
            ->with(
                ['option' => $optionId],
                null,
                null,
                [],
                null,
                1,
                null
            )
            ->once()
            ->andReturn($item);

        static::assertSame(
            $item,
            $this->subject->getByOption($optionId)
        );
    }

    public function testUpdateGameStateUpdates(): void
    {
        $state = GameStateEnum::RELOCATION;

        $database = $this->mock(Connection::class);

        $database->shouldReceive('update')
            ->with(
                GameConfig::TABLE_NAME,
                [
                    'value' => $state->value
                ],
                [
                    'option' => GameStateInterface::CONFIG_GAMESTATE
                ],
                [
                    'value' => ParameterType::INTEGER
                ]
            )
            ->once();

        $this->subject->updateGameState($state, $database);
    }
}
