<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreationConfigInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

class ShipCreationConfig implements SpacecraftCreationConfigInterface
{
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $buildplanRepository,
        private int $buildplanId
    ) {}

    #[Override]
    public function getSpacecraft(): ?SpacecraftInterface
    {
        return null;
    }

    #[Override]
    public function getSpecialSystemModules(): Collection
    {
        $buildplan = $this->buildplanRepository->find($this->buildplanId);
        if ($buildplan === null) {
            throw new RuntimeException(sprintf('buildplan with id %d not found', $this->buildplanId));
        }

        return $buildplan->getModulesByType(SpacecraftModuleTypeEnum::SPECIAL);
    }
}
