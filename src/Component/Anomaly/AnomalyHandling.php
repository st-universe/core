<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyHandlerInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;

final class AnomalyHandling implements AnomalyHandlingInterface
{
    private AnomalyRepositoryInterface $anomalyRepository;

    /** @var array<AnomalyHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<AnomalyHandlerInterface> $handlerList
     */
    public function __construct(
        AnomalyRepositoryInterface $anomalyRepository,
        array $handlerList
    ) {
        $this->anomalyRepository = $anomalyRepository;
        $this->handlerList = $handlerList;
    }

    public function processExistingAnomalies(): void
    {
        foreach ($this->anomalyRepository->findAll() as $anomaly) {
            if (!array_key_exists($anomaly->getAnomalyType()->getId(), $this->handlerList)) {
                throw new RuntimeException(sprintf('no handler defined for type: %d', $anomaly->getAnomalyType()->getId()));
            }

            $handler = $this->handlerList[$anomaly->getAnomalyType()->getId()];

            $handler->handleShipTick($anomaly);
            $this->decreaseLifespan($anomaly, $handler);
        }
    }

    public function createNewAnomalies(): void
    {
        foreach ($this->handlerList as $handler) {
            $handler->checkForCreation();
        }
    }

    private function decreaseLifespan(AnomalyInterface $anomaly, AnomalyHandlerInterface $handler): void
    {
        $remainingTicks = $anomaly->getRemainingTicks();

        if ($remainingTicks === 1) {
            $handler->letAnomalyDisappear($anomaly);
            $this->anomalyRepository->delete($anomaly);
        } else {
            $anomaly->setRemainingTicks($remainingTicks - 1);
            $this->anomalyRepository->save($anomaly);
        }
    }
}
