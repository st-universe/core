<?php

namespace Stu\Module\Ship\Lib;

use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TholianWebInterface;

interface TholianWebUtilInterface
{
    public function releaseSpacecraftFromWeb(SpacecraftWrapperInterface $wrapper): void;

    public function releaseAllShips(TholianWebInterface $web, SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory): void;

    public function removeWeb(TholianWebInterface $web): void;

    public function releaseWebHelper(ShipWrapperInterface $wrapper): void;

    public function resetWebHelpers(
        TholianWebInterface $web,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        bool $isFinished = false
    ): void;

    public function updateWebFinishTime(TholianWebInterface $web, ?int $helperModifier = null): ?int;

    public function isTargetOutsideFinishedTholianWeb(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): bool;

    public function isTargetInsideFinishedTholianWeb(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): bool;
}
