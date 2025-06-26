<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Override;
use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Location;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\AnomalyTypeRepositoryInterface;

final class AnomalyCreation implements AnomalyCreationInterface
{
    public function __construct(
        private AnomalyRepositoryInterface $anomalyRepository,
        private AnomalyTypeRepositoryInterface $anomalyTypeRepository
    ) {}

    #[Override]
    public function create(
        AnomalyTypeEnum $type,
        ?Location $location,
        ?Anomaly $parent = null,
        ?Object $dataObject = null
    ): Anomaly {

        $anomalyType = $this->anomalyTypeRepository->find($type->value);
        if ($anomalyType === null) {
            throw new RuntimeException(sprintf('no anomaly in database for type: %d', $type->value));
        }

        $anomaly = $this->anomalyRepository
            ->prototype()
            ->setAnomalyType($anomalyType)
            ->setRemainingTicks($anomalyType->getLifespanInTicks())
            ->setLocation($location);

        if ($parent !== null && $location !== null) {
            $parent->getChildren()->set($location->getId(), $anomaly);
            $anomaly->setParent($parent);
        }

        if ($dataObject !== null) {
            $json = json_encode($dataObject, JSON_THROW_ON_ERROR);
            $anomaly->setData($json);
        }

        if ($location !== null) {
            $location->addAnomaly($anomaly);
        }

        $this->anomalyRepository->save($anomaly);

        return $anomaly;
    }
}
