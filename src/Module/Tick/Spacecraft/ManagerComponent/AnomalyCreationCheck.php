<?php

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Stu\Component\Anomaly\AnomalyHandlingInterface;

class AnomalyCreationCheck implements ManagerComponentInterface
{
    public function __construct(private AnomalyHandlingInterface $anomalyHandling) {}

    #[\Override]
    public function work(): void
    {
        $this->anomalyHandling->createNewAnomalies();
    }
}
