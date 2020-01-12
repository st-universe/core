<?php

namespace Stu\Component\Queue\Message\Type;

use Stu\Component\Queue\Message\TransformableMessageInterface;

interface BuildingJobProcessMessageInterface extends TransformableMessageInterface
{
    public function getPlanetFieldId(): int;

    public function setPlanetFieldId(int $planetFieldId): BuildingJobProcessMessageInterface;
}
