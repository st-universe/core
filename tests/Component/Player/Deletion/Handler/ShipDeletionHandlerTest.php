<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class ShipDeletionHandlerTest extends MockeryTestCase
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
     * @var null|ShipDeletionHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->shipRemover = Mockery::mock(ShipRemoverInterface::class);
        $this->shipRepository = Mockery::mock(ShipRepositoryInterface::class);

        $this->handler = new ShipDeletionHandler(
            $this->shipRemover,
            $this->shipRepository
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
            ->with($ship)
            ->once();

        $this->handler->delete($user);
    }
}
