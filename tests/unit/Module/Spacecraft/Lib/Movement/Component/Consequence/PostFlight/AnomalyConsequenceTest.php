<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Mockery\MockInterface;
use Override;
use Stu\Component\Anomaly\AnomalyHandlingInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\StuTestCase;

class AnomalyConsequenceTest extends StuTestCase
{
    /** @var MockInterface&AnomalyHandlingInterface */
    private $anomalyHandling;

    private FlightConsequenceInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->anomalyHandling = $this->mock(AnomalyHandlingInterface::class);

        $this->subject = new AnomalyConsequence(
            $this->anomalyHandling
        );
    }

    public function testTrigger(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);

        $wrapper->shouldReceive('get->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->anomalyHandling->shouldReceive('handleIncomingSpacecraft')
            ->with($wrapper, $messages)
            ->once();

        $this->subject->trigger(
            $wrapper,
            $flightRoute,
            $messages
        );
    }
}
