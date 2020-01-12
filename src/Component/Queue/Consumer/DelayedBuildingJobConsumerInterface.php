<?php

namespace Stu\Component\Queue\Consumer;

interface DelayedBuildingJobConsumerInterface
{
    public function consume(): void;
}
