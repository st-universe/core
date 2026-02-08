<?php

namespace Stu\Module\Ship\Lib;

use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\TholianWeb;

interface TholianWebUtilInterface
{
    public function releaseSpacecraftFromWeb(SpacecraftWrapperInterface $wrapper): void;

    public function releaseAllShips(TholianWeb $web, SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory): void;

    public function removeWeb(TholianWeb $web): void;

    public function releaseWebHelper(ShipWrapperInterface $wrapper): void;

    public function resetWebHelpers(
        TholianWeb $web,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        bool $isFinished = false
    ): void;

    public function updateWebFinishTime(TholianWeb $web, ?int $helperModifier = null): ?int;

    public function isTargetOutsideFinishedTholianWeb(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): bool;

    public function isTargetInsideFinishedTholianWeb(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): bool;
}
