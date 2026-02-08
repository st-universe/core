<?php

namespace Stu\Component\Spacecraft\System\Control;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

trait GetTargetWrapperTrait
{
    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader*/
    protected function getTargetWrapper(
        SpacecraftWrapperInterface|int $target,
        bool $allowUplink,
        SpacecraftLoaderInterface $spacecraftLoader,
        GameControllerInterface $game
    ): SpacecraftWrapperInterface {
        if ($target instanceof SpacecraftWrapperInterface) {
            return $target;
        }

        return $spacecraftLoader->getWrapperByIdAndUser(
            $target,
            $game->getUser()->getId(),
            $allowUplink
        );
    }
}
