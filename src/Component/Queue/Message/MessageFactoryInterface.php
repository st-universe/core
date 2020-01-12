<?php

namespace Stu\Component\Queue\Message;

use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;

interface MessageFactoryInterface
{
    public function createBuildingJobProcessMessage(): BuildingJobProcessMessageInterface;
}
