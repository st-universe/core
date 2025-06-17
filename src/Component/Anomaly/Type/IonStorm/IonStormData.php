<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Stu\Module\Control\StuRandom;

class IonStormData
{
    public function __construct(
        public int $directionInDegrees = 0,
        public int $velocity = 0,
        public IonStormMovementType $movementType = IonStormMovementType::STATIC
    ) {}

    public function getHorizontalMovement(): int
    {
        return (int)round(sin(deg2rad($this->directionInDegrees)) * $this->velocity);
    }

    public function getVerticalMovement(): int
    {
        return (int)round(cos(deg2rad($this->directionInDegrees)) * $this->velocity);
    }

    public function changeMovement(StuRandom $stuRandom): IonStormData
    {
        $this->directionInDegrees = $stuRandom->rand(1, 360);
        $this->velocity = $stuRandom->rand(1, 5, true, 2);

        return $this;
    }

    public static function createRandomInstance(StuRandom $stuRandom): IonStormData
    {
        $instance = new IonStormData();
        $instance->movementType = $stuRandom->rand(1, 10) === 10 ? IonStormMovementType::VARIABLE : IonStormMovementType::STATIC;

        return $instance->changeMovement($stuRandom);
    }
}
