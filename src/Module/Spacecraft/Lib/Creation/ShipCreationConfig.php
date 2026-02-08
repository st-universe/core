<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

class ShipCreationConfig implements SpacecraftCreationConfigInterface
{
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $buildplanRepository,
        private int $buildplanId
    ) {}

    #[\Override]
    public function getSpacecraft(): ?Spacecraft
    {
        return null;
    }

    #[\Override]
    public function getSpecialSystemModules(): Collection
    {
        $buildplan = $this->buildplanRepository->find($this->buildplanId);
        if ($buildplan === null) {
            throw new RuntimeException(sprintf('buildplan with id %d not found', $this->buildplanId));
        }

        return $buildplan->getModulesByType(SpacecraftModuleTypeEnum::SPECIAL);
    }
}
