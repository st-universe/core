<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement;

use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface HandleManagersInterface
{
    /**
     * @param array<string, array<int|string, mixed>> $values
     *
     * @return array<string>
     */
    public function handle(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array;
}
