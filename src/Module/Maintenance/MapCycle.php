<?php

namespace Stu\Module\Maintenance;

use Override;
use Stu\Component\Map\MapEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class MapCycle implements MaintenanceHandlerInterface
{
    public function __construct(private MapRepositoryInterface $mapRepository, private UserLayerRepositoryInterface $userLayerRepository, private UserMapRepositoryInterface $userMapRepository, private CreateUserAwardInterface $createUserAward)
    {
    }

    #[Override]
    public function handle(): void
    {
        $userLayers = $this->userLayerRepository->getByMappingType(MapEnum::MAPTYPE_INSERT);
        foreach ($userLayers as $userLayer) {
            $user = $userLayer->getUser();
            $layer = $userLayer->getLayer();
            $fieldcount = $this->mapRepository->getAmountByLayer($layer);

            // if user has mapped everything in this layer
            if ($this->userMapRepository->getAmountByUser($user, $layer) >= $fieldcount) {
                $this->cycle($userLayer);
            }
        }
    }

    private function cycle(UserLayerInterface $userLayer): void
    {
        $userLayer->setMappingType(MapEnum::MAPTYPE_LAYER_EXPLORED);
        $this->userLayerRepository->save($userLayer);

        $this->userMapRepository->truncateByUserAndLayer($userLayer);

        $award = $userLayer->getLayer()->getAward();
        if ($award != null) {
            $this->createUserAward->createAwardForUser($userLayer->getUser(), $award);
        }
    }
}
