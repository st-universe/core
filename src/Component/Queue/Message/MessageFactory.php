<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message;

use Stu\Component\Queue\Message\Type\BuildingJobProcessMessage;
use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;

final class MessageFactory implements MessageFactoryInterface
{
    public function createBuildingJobProcessMessage(): BuildingJobProcessMessageInterface
    {
        return new BuildingJobProcessMessage();
    }
}
