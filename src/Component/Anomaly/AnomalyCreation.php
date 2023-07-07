<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use RuntimeException;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\AnomalyTypeRepositoryInterface;

final class AnomalyCreation implements AnomalyCreationInterface
{
    private AnomalyRepositoryInterface $anomalyRepository;

    private AnomalyTypeRepositoryInterface $anomalyTypeRepository;

    public function __construct(
        AnomalyRepositoryInterface $anomalyRepository,
        AnomalyTypeRepositoryInterface $anomalyTypeRepository
    ) {
        $this->anomalyRepository = $anomalyRepository;
        $this->anomalyTypeRepository = $anomalyTypeRepository;
    }

    public function create(
        int $anomalyType,
        MapInterface|StarSystemMapInterface $map
    ): void {
        $type = $this->anomalyTypeRepository->find($anomalyType);

        if ($type === null) {
            throw new RuntimeException(sprintf('no handler defined for type: %d', $anomalyType));
        }

        $anomaly = $this->anomalyRepository->prototype();
        $anomaly->setAnomalyType($type);
        $anomaly->setRemainingTicks($type->getLifespanInTicks());

        if ($map instanceof MapInterface) {
            $anomaly->setMap($map);
        } else {
            $anomaly->setStarsystemMap($map);
        }

        $this->anomalyRepository->save($anomaly);
    }
}
