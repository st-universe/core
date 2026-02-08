<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction;

use Mockery\MockInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\SpacecraftDestructionHandlerInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftDestructionTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;

    private MockInterface&SpacecraftDestructionHandlerInterface $deletionHandler1;

    private MockInterface&SpacecraftDestructionHandlerInterface $deletionHandler2;

    /** @var SpacecraftDestructionInterface */
    private $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);

        $this->subject = $this->mock(SpacecraftDestructionInterface::class);
        $this->deletionHandler1 = $this->mock(SpacecraftDestructionHandlerInterface::class);
        $this->deletionHandler2 = $this->mock(SpacecraftDestructionHandlerInterface::class);

        $this->subject = new SpacecraftDestruction(
            $this->spacecraftRepository,
            [$this->deletionHandler1, $this->deletionHandler2]
        );
    }

    public function testDestroyExpectCallOfAllHandlers(): void
    {
        $destroyer = $this->mock(SpacecraftDestroyerInterface::class);
        $destroyedShipWrapper = $this->mock(ShipWrapperInterface::class);
        $destroyedShip = $this->mock(Ship::class);
        $cause = SpacecraftDestructionCauseEnum::ALERT_RED;
        $informations = $this->mock(InformationInterface::class);

        $this->deletionHandler1->shouldReceive('handleSpacecraftDestruction')
            ->with(
                $destroyer,
                $destroyedShipWrapper,
                $cause,
                $informations
            )
            ->once();
        $this->deletionHandler2->shouldReceive('handleSpacecraftDestruction')
            ->with(
                $destroyer,
                $destroyedShipWrapper,
                $cause,
                $informations
            )
            ->once();

        $destroyedShipWrapper->shouldReceive('get')
            ->withNOArgs()
            ->once()
            ->andReturn($destroyedShip);
        $this->spacecraftRepository->shouldReceive('delete')
            ->with($destroyedShip)
            ->once();

        $this->subject->destroy(
            $destroyer,
            $destroyedShipWrapper,
            $cause,
            $informations
        );
    }
}
