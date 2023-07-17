<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\ModuleScreen\ModuleSelectorAddonInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;

final class ModuleSelectorAddonHull implements ModuleSelectorAddonInterface
{
    private TorpedoHullRepositoryInterface $torpedoHullRepository;

    private GradientColorInterface $gradientColor;

    /** @var array<int>|null */
    private ?array $interval = null;

    public function __construct(
        TorpedoHullRepositoryInterface $torpedoHullRepository,
        GradientColorInterface $gradientColor
    ) {
        $this->torpedoHullRepository = $torpedoHullRepository;
        $this->gradientColor = $gradientColor;
    }

    public function calculateGradientColor(int $modificator): string
    {
        if ($this->interval === null) {
            $this->interval = $this->torpedoHullRepository->getModificatorMinAndMax();
        }

        [$lowest, $highest] = $this->interval;

        return $this->gradientColor->calculateGradientColor($modificator, $lowest, $highest);
    }
}
