<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Stu\Component\Map\MapEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLayerInterface;

trait LayerExplorationTrait
{
    protected function hasExplored(UserInterface $user, LayerInterface $layer): bool
    {
        if (!$this->hasSeen($user, $layer)) {
            return false;
        }

        /** @var null|UserLayerInterface */
        $userLayer = $user->getUserLayers()->get($layer->getId());

        return $userLayer === null
            ? false
            : $userLayer->getMappingType() === MapEnum::MAPTYPE_LAYER_EXPLORED;
    }

    protected function hasSeen(UserInterface $user, LayerInterface $layer): bool
    {
        return $user->getUserLayers()->containsKey($layer->getId());
    }
}
