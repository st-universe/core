<?php

namespace Stu\Component\Admin\Reset;

use ReflectionAttribute;
use ReflectionClass;
use Stu\Orm\Attribute\TruncateOnGameReset;

class EntityReflectionEntry
{
    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public function __construct(
        private readonly string $className,
        private readonly ReflectionClass $reflectionClass
    ) {}

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getShortName(): string
    {
        return $this->reflectionClass->getShortName();
    }

    public function hasTruncationAttribute(): bool
    {
        return $this->getTruncateAttribute() !== null;
    }

    public function getPriority(): int
    {
        return $this->getTruncateAttribute()?->newInstance()->priority ?? 0;
    }

    /** @return ReflectionAttribute<TruncateOnGameReset> */
    private function getTruncateAttribute(): ?ReflectionAttribute
    {
        $attribute = current($this->reflectionClass->getAttributes(TruncateOnGameReset::class));

        return $attribute === false ? null : $attribute;
    }
}
