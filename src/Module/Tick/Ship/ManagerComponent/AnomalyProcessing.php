<?php

namespace Stu\Module\Tick\Ship\ManagerComponent;

use Stu\Component\Anomaly\AnomalyHandlingInterface;

class AnomalyProcessing implements ManagerComponentInterface
{
    private AnomalyHandlingInterface $anomalyHandling;

    public function __construct(AnomalyHandlingInterface $anomalyHandling)
    {
        $this->anomalyHandling = $anomalyHandling;
    }

    public function work(): void
    {
        $this->anomalyHandling->processExistingAnomalies();
    }
}
