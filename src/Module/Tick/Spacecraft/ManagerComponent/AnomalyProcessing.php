<?php

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Override;
use Stu\Component\Anomaly\AnomalyHandlingInterface;

class AnomalyProcessing implements ManagerComponentInterface
{
    public function __construct(private AnomalyHandlingInterface $anomalyHandling) {}

    #[Override]
    public function work(): void
    {
        $this->anomalyHandling->processExistingAnomalies();
    }
}
