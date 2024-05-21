<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Destruction;

use Mockery\MockInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\Handler\ShipDestructionHandlerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\StuTestCase;

class ShipDestructionTest extends StuTestCase
{
    /**
     * @var MockInterface|ShipDestructionHandlerInterface
     */
    private $deletionHandler1;

    /**
     * @var MockInterface|ShipDestructionHandlerInterface
     */
    private $deletionHandler2;

    /**
     * @var ShipDestructionInterface
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = $this->mock(ShipDestructionInterface::class);
        $this->deletionHandler1 = $this->mock(ShipDestructionHandlerInterface::class);
        $this->deletionHandler2 = $this->mock(ShipDestructionHandlerInterface::class);

        $this->subject = new ShipDestruction(
            [$this->deletionHandler1, $this->deletionHandler2]
        );
    }

    public function testDestroyExpectCallOfAllHandlers(): void
    {
        $destroyer = $this->mock(ShipDestroyerInterface::class);
        $destroyedShipWrapper = $this->mock(ShipWrapperInterface::class);
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

        $this->subject->destroy(
            $destroyer,
            $destroyedShipWrapper,
            $cause,
            $informations
        );
    }
}
