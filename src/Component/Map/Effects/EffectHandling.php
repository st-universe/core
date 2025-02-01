<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects;

use Override;
use RuntimeException;
use Stu\Component\Map\Effects\Type\EffectHandlerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class EffectHandling implements EffectHandlingInterface
{
    /**
     * @param array<string, EffectHandlerInterface> $handlerList
     */
    public function __construct(
        private array $handlerList
    ) {}

    #[Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        foreach ($wrapper->get()->getLocation()->getFieldType()->getEffects() as $effect) {
            $this->getHandler($effect)->handleSpacecraftTick($wrapper, $information);
        }
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        foreach ($wrapper->get()->getLocation()->getFieldType()->getEffects() as $effect) {
            $this->getHandler($effect)->handleIncomingSpacecraft($wrapper, $messages);
        }
    }

    private function getHandler(FieldTypeEffectEnum $effect): EffectHandlerInterface
    {
        if (!array_key_exists($effect->value, $this->handlerList)) {
            throw new RuntimeException(sprintf('no handler defined for type: %d', $effect->value));
        }

        $handler = $this->handlerList[$effect->value];

        return $handler;
    }
}
