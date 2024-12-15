<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\Creation\SpecialSystemsProviderInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

class ShipSpecialSystemsProvider implements SpecialSystemsProviderInterface
{
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $buildplanRepository,
        private int $buildplanId
    ) {}

    public function getSpecialSystemModules(): Collection
    {
        $buildplan = $this->buildplanRepository->find($this->buildplanId);
        if ($buildplan === null) {
            throw new RuntimeException(sprintf('buildplan with id %d not found', $this->buildplanId));
        }

        return $buildplan->getModulesByType(SpacecraftModuleTypeEnum::SPECIAL);
    }
}
