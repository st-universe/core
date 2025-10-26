<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects;

use RuntimeException;
use Stu\Component\Map\Effects\Type\EffectHandlerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

final class EffectHandling implements EffectHandlingInterface
{
    /**
     * @param array<string, EffectHandlerInterface> $handlerList
     */
    public function __construct(
        private array $handlerList
    ) {}

    #[\Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        $this->walkEffects($wrapper->get()->getLocation(), function (EffectHandlerInterface $handler) use ($wrapper, $information): void {
            $handler->handleSpacecraftTick($wrapper, $information);
        });
    }

    #[\Override]
    public function addFlightInformationForActiveEffects(Location $location, MessageCollectionInterface $messages): void
    {
        $this->walkEffects($location, function (EffectHandlerInterface $handler) use ($location, $messages): void {
            $handler->addFlightInformation($location, $messages);
        });
    }

    #[\Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $this->walkEffects($wrapper->get()->getLocation(), function (EffectHandlerInterface $handler) use ($wrapper, $messages): void {
            $handler->handleIncomingSpacecraft($wrapper, $messages);
        });
    }

    private function walkEffects(Location $location, callable $func): void
    {
        foreach ($location->getFieldType()->getEffects() as $effect) {
            if ($effect->hasHandler()) {
                $func($this->getHandler($effect));
            }
        }
    }

    private function getHandler(FieldTypeEffectEnum $effect): EffectHandlerInterface
    {
        if (!array_key_exists($effect->value, $this->handlerList)) {
            throw new RuntimeException(sprintf('no handler defined for type: %d', $effect->value));
        }

        return $this->handlerList[$effect->value];
    }
}
