<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Destruction;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\Handler\ShipDestructionHandlerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipDestructionTest extends StuTestCase
{
    /** @var MockInterface|ShipRepositoryInterface */
    private $shipRepository;

    /** @var MockInterface|ShipDestructionHandlerInterface */
    private $deletionHandler1;

    /** @var MockInterface|ShipDestructionHandlerInterface */
    private $deletionHandler2;

    /** @var ShipDestructionInterface */
    private $subject;

    #[Override]
    public function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);

        $this->subject = $this->mock(ShipDestructionInterface::class);
        $this->deletionHandler1 = $this->mock(ShipDestructionHandlerInterface::class);
        $this->deletionHandler2 = $this->mock(ShipDestructionHandlerInterface::class);

        $this->subject = new ShipDestruction(
            $this->shipRepository,
            [$this->deletionHandler1, $this->deletionHandler2]
        );
    }

    public function testDestroyExpectCallOfAllHandlers(): void
    {
        $destroyer = $this->mock(ShipDestroyerInterface::class);
        $destroyedShipWrapper = $this->mock(ShipWrapperInterface::class);
        $destroyedShip = $this->mock(ShipInterface::class);
        $cause = ShipDestructionCauseEnum::ALERT_RED;
        $informations = $this->mock(InformationInterface::class);

        $this->deletionHandler1->shouldReceive('handleShipDestruction')
            ->with(
                $destroyer,
                $destroyedShipWrapper,
                $cause,
                $informations
            )
            ->once();
        $this->deletionHandler2->shouldReceive('handleShipDestruction')
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
        $this->shipRepository->shouldReceive('save')
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
