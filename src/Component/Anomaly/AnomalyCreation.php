<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
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
        AnomalyTypeEnum $type,
        MapInterface|StarSystemMapInterface $map
    ): AnomalyInterface {
        $anomalyType = $this->anomalyTypeRepository->find($type->value);

        if ($anomalyType === null) {
            throw new RuntimeException(sprintf('no anomaly in database for type: %d', $type->value));
        }

        $anomaly = $this->anomalyRepository->prototype();
        $anomaly->setAnomalyType($anomalyType);
        $anomaly->setRemainingTicks($anomalyType->getLifespanInTicks());

        if ($map instanceof MapInterface) {
            $anomaly->setMap($map);
        } else {
            $anomaly->setStarsystemMap($map);
        }

        $this->anomalyRepository->save($anomaly);

        return $anomaly;
    }
}
