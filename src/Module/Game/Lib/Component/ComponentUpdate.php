<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

final class ComponentUpdate
{
    public function __construct(private ComponentEnum $component, private bool $isInstantUpdate)
    {
    }

    public function getComponent(): ComponentEnum
    {
        return $this->component;
    }

    public function isInstantUpdate(): bool
    {
        return $this->isInstantUpdate;
    }
}
