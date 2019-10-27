<?php

namespace Stu\Module\Colony\Lib;

interface BuildingMassActionConfigurationInterface
{
    /**
     * @return callable[]
     */
    public function getConfigurations(): array;
}
