<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use OutOfBoundsException;
use Stu\Component\Anomaly\Type\AnomalyHandlerInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;
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

    #[\Override]
    public function processExistingAnomalies(): void
    {
        foreach ($this->anomalyRepository->findAllRoot() as $root) {

            $handler = $this->getHandler($root);
            $handler->handleSpacecraftTick($root);
            $this->decreaseLifespan($root, $handler);
        }
    }

    #[\Override]
    public function createNewAnomalies(): void
    {
        foreach ($this->handlerList as $handler) {
            $handler->checkForCreation();
        }
    }

    #[\Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        foreach ($wrapper->get()->getLocation()->getAnomalies() as $anomaly) {
            $this->getHandler($anomaly)->handleIncomingSpacecraft($wrapper, $anomaly, $messages);
        }
    }

    private function decreaseLifespan(Anomaly $anomaly, AnomalyHandlerInterface $handler): void
    {
        $remainingTicks = $anomaly->getRemainingTicks();

        if ($remainingTicks <= 1) {
            $handler->letAnomalyDisappear($anomaly);
            $this->anomalyRepository->delete($anomaly);
        } else {
            $anomaly->changeRemainingTicks(-1);
            $this->anomalyRepository->save($anomaly);
        }

        foreach ($anomaly->getChildren() as $child) {
            $this->decreaseLifespan($child, $handler);
        }
    }

    private function getHandler(Anomaly $anomaly): AnomalyHandlerInterface
    {
        $type = $anomaly->getAnomalyType()->getId();

        if (!array_key_exists($type, $this->handlerList)) {
            throw new OutOfBoundsException(sprintf('no handler defined for type: %d', $type));
        }

        return $this->handlerList[$type];
    }
}
