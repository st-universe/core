<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\StuTestCase;

class ShipBuildplanDeletionHandlerTest extends StuTestCase
{
    /** @var MockInterface&ShipBuildplanRepositoryInterface */
    private MockInterface $shipBuildplanRepository;

    private ShipBuildplanDeletionHandler $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipBuildplanRepository = $this->mock(ShipBuildplanRepositoryInterface::class);

        $this->subject = new ShipBuildplanDeletionHandler(
            $this->shipBuildplanRepository
        );
    }

    public function testDeleteDeletesBuildplans(): void
    {
        $user = $this->mock(UserInterface::class);
        $shipBuildplan = $this->mock(ShipBuildplanInterface::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->shipBuildplanRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$shipBuildplan]);
        $this->shipBuildplanRepository->shouldReceive('delete')
            ->with($shipBuildplan)
            ->once();

        $this->subject->delete($user);
    }
}
