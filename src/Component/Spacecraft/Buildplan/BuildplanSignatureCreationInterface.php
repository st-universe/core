<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Buildplan;

use Stu\Orm\Entity\Module;

interface BuildplanSignatureCreationInterface
{
    /**
     * @param array<Module> $modules
     */
    public function createSignature(array $modules, int $crewUsage): string;

    /**
     * @param array<int> $moduleIds
     */
    public function createSignatureByModuleIds(array &$moduleIds, int $crewUsage): string;
}
