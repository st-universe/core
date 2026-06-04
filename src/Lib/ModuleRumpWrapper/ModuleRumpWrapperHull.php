<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;

final class ModuleRumpWrapperHull extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    #[\Override]
    public function getValue(?Module $module = null): int
    {
        $module ??= current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return new ModuleValueCalculator()->calculateModuleValue(
            $this->rump,
            $module,
            $this->rumpBaseValues->getBaseHull()
        );
    }

    #[\Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::HULL;
    }

    #[\Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        $condition = $spacecraft->getCondition();
        $actualHull = $condition->getHull();
        $oldMaxHull = $spacecraft->getMaxHull();
        $newMaxHull = $this->getValue();

        if ($oldMaxHull === 0) {
            $condition->setHull($newMaxHull);
        } else {
            $condition->setHull(min(
                $newMaxHull,
                max(0, (int) round($actualHull * $newMaxHull / $oldMaxHull))
            ));
        }

        $spacecraft->setMaxHull($newMaxHull);
    }
}
