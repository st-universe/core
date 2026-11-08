<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Buildplan;

use Override;
use Stu\Orm\Entity\Module;

class BuildplanSignatureCreation implements BuildplanSignatureCreationInterface
{
    /** @param array<int, int> $qualities */
    #[Override]
    public function createSignature(array $modules, int $crewUsage, array $qualities = []): string
    {
        $ids = array_map(fn (Module $module): int => $module->getId(), $modules);

        $qualities = array_filter($qualities, fn (int $steps): bool => $steps > 0);
        if ($qualities === []) {
            return $this->createSignatureByModuleIds($ids, $crewUsage);
        }
        ksort($qualities);
        return md5($this->createSignatureByModuleIds($ids, $crewUsage) . json_encode($qualities, JSON_THROW_ON_ERROR));
    }

    #[Override]
    public function createSignatureByModuleIds(array &$moduleIds, int $crewUsage): string
    {
        sort($moduleIds);

        return md5(implode('_', $moduleIds) . '_' . $crewUsage);
    }
}
