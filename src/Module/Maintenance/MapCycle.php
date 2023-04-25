<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Map\MapEnum;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class MapCycle implements MaintenanceHandlerInterface
{
    private MapRepositoryInterface $mapRepository;

    private UserLayerRepositoryInterface $userLayerRepository;

    private UserMapRepositoryInterface $userMapRepository;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        UserLayerRepositoryInterface $userLayerRepository,
        UserMapRepositoryInterface $userMapRepository
    ) {
        $this->mapRepository = $mapRepository;
        $this->userLayerRepository = $userLayerRepository;
        $this->userMapRepository = $userMapRepository;
    }

    public function handle(): void
    {
        $userLayers = $this->userLayerRepository->getByMappingType(MapEnum::MAPTYPE_INSERT);
        foreach ($userLayers as $userLayer) {
            $user = $userLayer->getUser();
            $layer = $userLayer->getLayer();
            $fieldcount = $this->mapRepository->getAmountByLayer($layer->getId());

            // if user has mapped everything in this layer
            if ($this->userMapRepository->getAmountByUser($user->getId(), $layer->getId()) >= $fieldcount) {
                $this->cycle($userLayer);
            }
        }
    }

    private function cycle(UserLayerInterface $userLayer)
    {
        $userLayer->setMappingType(MapEnum::MAPTYPE_LAYER_EXPLORED);
        $this->userLayerRepository->save($userLayer);

        $this->userMapRepository->truncateByUserAndLayer($userLayer->getUser()->getId(), $userLayer->getLayer()->getId());
    }
}
