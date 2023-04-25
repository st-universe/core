<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

use function PHPUnit\Framework\assertEmpty;

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
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->handler = new ShipDeletionHandler(
            $this->shipRemover,
            $this->shipRepository,
            $this->shipSystemManager,
            $this->shipWrapperFactory,
            $this->entityManager
        );
    }

    public function testDeleteDeletesUserShips(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $ship = Mockery::mock(ShipInterface::class);
        $wrapper = Mockery::mock(ShipWrapperInterface::class);
        $tractoredShip = Mockery::mock(ShipInterface::class);
        $dockedShip = Mockery::mock(ShipInterface::class);

        $dockedShips = new ArrayCollection([$dockedShip]);

        $ship->shouldReceive('getTradePost')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShip);

        $ship->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->twice()
            ->andReturn($dockedShips);

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

        $dockedShip->shouldReceive('setDockedTo')
            ->with(null)
            ->once();
        $dockedShip->shouldReceive('setDockedToId')
            ->with(null)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($dockedShip)
            ->once();

        $this->shipRepository->shouldReceive('getByUser')
            ->with($user)
            ->once()
            ->andReturn([$ship]);

        $this->shipRemover->shouldReceive('remove')
            ->with($ship, true)
            ->once();

        $this->handler->delete($user);

        assertEmpty($dockedShips);
    }
}
