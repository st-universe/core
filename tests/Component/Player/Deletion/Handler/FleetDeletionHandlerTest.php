<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

class FleetDeletionHandlerTest extends MockeryTestCase
{
    /**
     * @var null|FleetRepositoryInterface|MockInterface
     */
    private $fleetRepository;

    /**
     * @var null|FleetDeletionHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->fleetRepository = Mockery::mock(FleetRepositoryInterface::class);

        $this->handler = new FleetDeletionHandler(
            $this->fleetRepository
        );
    }

    public function testDeleteDeletesFleets(): void
    {
        $user = Mockery::mock(UserInterface::class);

        $this->fleetRepository->shouldReceive('truncateByUser')
            ->with($user)
            ->once();

        $this->handler->delete($user);
    }
}
