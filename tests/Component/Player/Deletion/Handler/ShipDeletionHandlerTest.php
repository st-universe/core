<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
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
     * @var null|MockInterface|ShipSystemManagerInterface
     */
    private $shipSystemManager;

    /**
     * @var null|MockInterface|ShipWrapperFactoryInterface
     */
    private $shipWrapperFactory;

    /**
     * @var null|MockInterface|ShipUndockingInterface
     */
    private $shipUndocking;

    /**
     * @var null|MockInterface|EntityManagerInterface
     */
    private $entityManager;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->shipRemover = $this->mock(ShipRemoverInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);
        $this->shipUndocking = $this->mock(ShipUndockingInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->handler = new ShipDeletionHandler(
            $this->shipRemover,
            $this->shipRepository,
            $this->shipSystemManager,
            $this->shipWrapperFactory,
            $this->shipUndocking,
            $this->entityManager
        );
    }

    public function testDeleteDeletesUserShips(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $ship = Mockery::mock(ShipInterface::class);
        $wrapper = Mockery::mock(ShipWrapperInterface::class);
        $tractoredShip = Mockery::mock(ShipInterface::class);

        $ship->shouldReceive('getTradePost')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShip);

        $this->shipWrapperFactory->shouldReceive('wrapShip')
            ->with($ship)
            ->once()
            ->andReturn($wrapper);

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM,
                true
            )
            ->once();

        $this->shipRepository->shouldReceive('getByUser')
            ->with($user)
            ->once()
            ->andReturn([$ship]);

        $this->shipRemover->shouldReceive('remove')
            ->with($ship, true)
            ->once();

        $this->shipUndocking->shouldReceive('undockAllDocked')
            ->with($ship)
            ->once()
            ->andReturn(true);

        $this->handler->delete($user);
    }
}
