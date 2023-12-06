<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

final class ComponentUpdate
{
    private ComponentEnum $component;

    private bool $isInstantUpdate;

    public function __construct(ComponentEnum $component, bool $isInstantUpdate)
    {
        $this->component = $component;
        $this->isInstantUpdate = $isInstantUpdate;
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
