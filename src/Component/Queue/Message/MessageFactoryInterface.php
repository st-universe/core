<?php

namespace Stu\Component\Queue\Message;

use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;
use Stu\Component\Queue\Message\Type\TerraformingJobProcessMessageInterface;

interface MessageFactoryInterface
{
    public function createBuildingJobProcessMessage(): BuildingJobProcessMessageInterface;

    public function createTerraformingJobProcessMessage(): TerraformingJobProcessMessageInterface;
}
