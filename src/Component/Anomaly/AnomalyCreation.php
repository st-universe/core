<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Override;
use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\AnomalyTypeRepositoryInterface;

final class AnomalyCreation implements AnomalyCreationInterface
{
    public function __construct(private AnomalyRepositoryInterface $anomalyRepository, private AnomalyTypeRepositoryInterface $anomalyTypeRepository)
    {
    }

    #[Override]
    public function create(
        AnomalyTypeEnum $type,
        LocationInterface $location
    ): AnomalyInterface {
        $anomalyType = $this->anomalyTypeRepository->find($type->value);

        if ($anomalyType === null) {
            throw new RuntimeException(sprintf('no anomaly in database for type: %d', $type->value));
        }

        $anomaly = $this->anomalyRepository->prototype();
        $anomaly->setAnomalyType($anomalyType);
        $anomaly->setRemainingTicks($anomalyType->getLifespanInTicks());
        $anomaly->setLocation($location);

        $this->anomalyRepository->save($anomaly);

        return $anomaly;
    }
}
