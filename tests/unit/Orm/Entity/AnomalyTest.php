<?php

declare(strict_types=1);

namespace Stu\Tests\Unit\Orm\Entity;

use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyType;
use Stu\Orm\Entity\Location;
use Stu\StuTestCase;

class AnomalyTest extends StuTestCase
{
    public function testSetLocation(): void
    {
        $location = $this->mock(Location::class);
        $oldLocation = $this->mock(Location::class);
        $anomalyType = $this->mock(AnomalyType::class);

        $anomaly = new Anomaly();
        $anomaly->setAnomalyType($anomalyType);
        $anomalyType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);


        $oldLocation->shouldReceive('getAnomalies')
            ->andReturn(new \Doctrine\Common\Collections\ArrayCollection());

        $location->shouldReceive('getAnomalies')
            ->andReturn(new \Doctrine\Common\Collections\ArrayCollection());

        $anomaly->setLocation($oldLocation);
        $anomaly->setLocation($location);

        $this->assertSame($location, $anomaly->getLocation());
    }

    public function testSetParent(): void
    {
        $parent = $this->mock(Anomaly::class);
        $location = $this->mock(Location::class);

        $anomaly = new Anomaly();

        $parent->shouldReceive('getChildren')
            ->andReturn(new \Doctrine\Common\Collections\ArrayCollection());

        $location->shouldReceive('getId')
            ->andReturn(42);

        $anomaly->setParent($parent, $location);

        $this->assertSame($parent, $anomaly->getParent());
    }
}
