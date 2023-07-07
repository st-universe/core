<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

interface AnomalyHandlingInterface
{
    public function processExistingAnomalies(): void;

    public function createNewAnomalies(): void;
}
