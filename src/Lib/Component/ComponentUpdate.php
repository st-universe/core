<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

final class ComponentUpdate
{
    public function __construct(
        private ComponentEnumInterface $componentEnum,
        private bool $isInstantUpdate
    ) {}

    public function getComponentEnum(): ComponentEnumInterface
    {
        return $this->componentEnum;
    }

    public function isInstantUpdate(): bool
    {
        return $this->isInstantUpdate;
    }
}
