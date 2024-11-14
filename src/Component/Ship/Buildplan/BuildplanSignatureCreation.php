<?php

namespace Stu\Component\Ship\Buildplan;

use Stu\Orm\Entity\ModuleInterface;

class BuildplanSignatureCreation implements BuildplanSignatureCreationInterface
{

    public function createSignature(array $modules, int $crewUsage): string
    {
        $ids = array_map(fn(ModuleInterface $module): int => $module->getId(), $modules);

        return $this->createSignatureByModuleIds($ids, $crewUsage);
    }

    public function createSignatureByModuleIds(array &$moduleIds, int $crewUsage): string
    {
        sort($moduleIds);

        return md5(implode('_', $moduleIds) . '_' . $crewUsage);
    }
}
