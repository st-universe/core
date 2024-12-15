<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Override;
use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyHandlerInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;

final class AnomalyHandling implements AnomalyHandlingInterface
{
    /**
     * @param array<int, AnomalyHandlerInterface> $handlerList
     */
    public function __construct(
        private AnomalyRepositoryInterface $anomalyRepository,
        private array $handlerList
    ) {}

    #[Override]
    public function processExistingAnomalies(): void
    {
        foreach ($this->anomalyRepository->findAllActive() as $anomaly) {
            $type = $anomaly->getAnomalyType()->getId();

            if (!array_key_exists($type, $this->handlerList)) {
                throw new RuntimeException(sprintf('no handler defined for type: %d', $type));
            }

            $handler = $this->handlerList[$type];

            $handler->handleSpacecraftTick($anomaly);
            $this->decreaseLifespan($anomaly, $handler);
        }
    }

    #[Override]
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
        }
        $anomaly->setRemainingTicks($remainingTicks - 1);
        $this->anomalyRepository->save($anomaly);
    }
}
