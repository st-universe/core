<?php

namespace Stu\Component\Spacecraft\Buildplan;

use Stu\Orm\Entity\ModuleInterface;

interface BuildplanSignatureCreationInterface
{
    /**
     * @param array<ModuleInterface> $modules
     */
    public function createSignature(array $modules, int $crewUsage): string;

    /**
     * @param array<int> $moduleIds
     */
    public function createSignatureByModuleIds(array &$moduleIds, int $crewUsage): string;
}
