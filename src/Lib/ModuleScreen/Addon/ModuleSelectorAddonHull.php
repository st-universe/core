<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Lib\ModuleScreen\GradientColor;
use Stu\Lib\ModuleScreen\ModuleSelectorAddonInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;

final class ModuleSelectorAddonHull implements ModuleSelectorAddonInterface
{
    private TorpedoHullRepositoryInterface $torpedoHullRepository;

    public function __construct(TorpedoHullRepositoryInterface $torpedoHullRepository)
    {
        $this->torpedoHullRepository = $torpedoHullRepository;
    }

    public function calculateGradientColor(int $modificator): string
    {
        [$lowest, $highest] = $this->torpedoHullRepository->getModificatorMinAndMax();
        return GradientColor::calculateGradientColor($modificator, $lowest, $highest);
    }
}
