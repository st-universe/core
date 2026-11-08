<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use InvalidArgumentException;
use request;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Storage;

final class ModuleQualitySelection
{
    public static function readCommodity(int $commodityId): ?int
    {
        $value = request::postArray('quality')[$commodityId] ?? 'all';
        if ($value === 'all') {
            return null;
        }
        $steps = is_numeric($value) ? (int) $value : -1;
        return $steps >= 0 && $steps <= 70 ? $steps : -1;
    }

    public static function getSelectedAmount(Storage $storage, string|int $wanted, ?int $quality): int
    {
        if ($wanted !== 'max' && !is_numeric($wanted)) {
            return 0;
        }
        $available = $storage->getAmountForQuality($quality);
        return $wanted === 'max' ? $available : min($available, max(0, (int) $wanted));
    }

    public static function matchesPlan(Module $module, SpacecraftBuildplan $plan, int $quality): bool
    {
        foreach ($plan->getModulesByType($module->getType()) as $oldModule) {
            if ($oldModule->getId() === $module->getId()) {
                return $plan->getModuleImprovementSteps($module) === $quality;
            }
        }
        return false;
    }

    /**
     * @param array<Module> $modules
     * @return array{array<int, int>, string}
     */
    public static function signature(BuildplanSignatureCreationInterface $creator, array $modules, int $crewUsage, ?SpacecraftBuildplan $plan = null): array
    {
        $qualities = self::read($modules, $plan);
        return [$qualities, self::createSignature($creator, $modules, $crewUsage, $qualities)];
    }

    /**
     * @param array<Module> $modules
     * @param array<int, int> $qualities
     */
    public static function createSignature(BuildplanSignatureCreationInterface $creator, array $modules, int $crewUsage, array $qualities): string
    {
        return array_filter($qualities) === []
            ? $creator->createSignature($modules, $crewUsage)
            : $creator->createSignature($modules, $crewUsage, $qualities);
    }

    /**
     * @param array<Module> $modules
     * @return array<int, int>
     */
    public static function read(array $modules, ?SpacecraftBuildplan $buildplan = null): array
    {
        $requested = request::postArray('module_quality');
        $qualities = [];
        foreach ($modules as $module) {
            $steps = (int) ($requested[$module->getId()] ?? $buildplan?->getModuleImprovementSteps($module) ?? 0);
            if ($steps < 0 || $steps > 70) {
                throw new InvalidArgumentException('Invalid module quality');
            }
            $qualities[$module->getId()] = $steps;
        }
        return $qualities;
    }
}
