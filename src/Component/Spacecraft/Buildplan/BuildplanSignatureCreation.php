<?php

namespace Stu\Component\Spacecraft\Buildplan;

use Stu\Orm\Entity\Module;

class BuildplanSignatureCreation implements BuildplanSignatureCreationInterface
{
    #[\Override]
    public function createSignature(array $modules, int $crewUsage): string
    {
        $ids = array_map(fn (Module $module): int => $module->getId(), $modules);

        return $this->createSignatureByModuleIds($ids, $crewUsage);
    }

    #[\Override]
    public function createSignatureByModuleIds(array &$moduleIds, int $crewUsage): string
    {
        sort($moduleIds);

        return md5(implode('_', $moduleIds) . '_' . $crewUsage);
    }
}
