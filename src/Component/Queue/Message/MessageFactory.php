<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message;

use Stu\Component\Queue\Message\Type\BuildingJobProcessMessage;
use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;
use Stu\Component\Queue\Message\Type\TerraformingJobProcessMessage;
use Stu\Component\Queue\Message\Type\TerraformingJobProcessMessageInterface;

final class MessageFactory implements MessageFactoryInterface
{
    public function createBuildingJobProcessMessage(): BuildingJobProcessMessageInterface
    {
        return new BuildingJobProcessMessage();
    }

    public function createTerraformingJobProcessMessage(): TerraformingJobProcessMessageInterface
    {
        return new TerraformingJobProcessMessage();
    }
}
