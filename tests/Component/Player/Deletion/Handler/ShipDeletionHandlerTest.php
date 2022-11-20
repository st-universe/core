<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipDeletionHandlerTest extends StuTestCase
{
    /**
     * @var null|MockInterface|ShipRemoverInterface
     */
    private $shipRemover;

    /**
     * @var null|MockInterface|ShipRepositoryInterface
     */
    private $shipRepository;

    /**
     * @var null|MockInterface|EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var null|ShipDeletionHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->shipRemover = $this->mock(ShipRemoverInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->handler = new ShipDeletionHandler(
            $this->shipRemover,
            $this->shipRepository,
            $this->entityManager
        );
    }

    public function testDeleteDeletesUser(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $ship = Mockery::mock(ShipInterface::class);

        $this->shipRepository->shouldReceive('getByUser')
            ->with($user)
            ->once()
            ->andReturn([$ship]);

        $this->shipRemover->shouldReceive('remove')
            ->with($ship, true)
            ->once();

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->handler->delete($user);
    }
}
