<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ManagerInterface
{
    /**
     * @param array<string, array<int|string, mixed>> $values
     *
     * @return array<string>
     */
    public function manage(
        SpacecraftWrapperInterface $wrapper,
        array $values,
        ManagerProviderInterface $managerProvider
    ): array;
}
