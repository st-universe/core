<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Override;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;

final class ModuleSelectorAddonHull implements ModuleSelectorAddonInterface
{
    /** @var array<int>|null */
    private ?array $interval = null;

    public function __construct(private TorpedoHullRepositoryInterface $torpedoHullRepository, private GradientColorInterface $gradientColor)
    {
    }

    //public function calculateGradientColor(int $modificator): string
    #[Override]
    public function getModificators(Module $module): array
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
