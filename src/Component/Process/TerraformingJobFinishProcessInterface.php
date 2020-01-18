<?php

namespace Stu\Component\Process;

use Stu\Component\Queue\Message\Type\TerraformingJobProcessMessageInterface;

interface TerraformingJobFinishProcessInterface
{
    public function work(TerraformingJobProcessMessageInterface $message): void;
}
