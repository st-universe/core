<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Stu\Component\Map\MapEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLayer;

trait LayerExplorationTrait
{
    protected function hasExplored(User $user, Layer $layer): bool
    {
        if (!$this->hasSeen($user, $layer)) {
            return false;
        }

        /** @var null|UserLayer */
        $userLayer = $user->getUserLayers()->get($layer->getId());

        return $userLayer === null
            ? false
            : $userLayer->getMappingType() === MapEnum::MAPTYPE_LAYER_EXPLORED;
    }

    protected function hasSeen(User $user, Layer $layer): bool
    {
        return $user->getUserLayers()->containsKey($layer->getId());
    }
}
