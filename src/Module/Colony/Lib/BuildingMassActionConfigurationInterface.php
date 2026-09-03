<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

interface BuildingMassActionConfigurationInterface
{
    /**
     * @return callable[]
     */
    public function getConfigurations(): array;
}
