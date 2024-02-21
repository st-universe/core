<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Entity\ModuleInterface;
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

    //public function calculateGradientColor(int $modificator): string
    public function getModificators(ModuleInterface $module): array
    {
        if ($this->interval === null) {
            $this->interval = $this->torpedoHullRepository->getModificatorMinAndMax();
        }

        [$lowest, $highest] = $this->interval;

        $result = [];
        foreach ($module->getTorpedoHull() as $torpedoHull) {

            $modificator = $torpedoHull->getModificator();
            $result[] = [
                'torpedoType' => $torpedoHull->getTorpedo(),
                'gradientColor' => $this->gradientColor->calculateGradientColor(
                    $modificator,
                    $lowest,
                    $highest
                ),
                'modificator' => $modificator
            ];
        }

        return $result;
    }
}
