<?php

namespace Stu\Component\Process;

use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;

interface BuildingJobFinishProcessInterface
{
    public function work(BuildingJobProcessMessageInterface $message): void;
}
