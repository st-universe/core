<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class UserYRow extends YRow
{
    private UserInterface $user;

    private MapRepositoryInterface $mapRepository;
    private StarmapUiFactoryInterface $starmapUiFactory;

    public function __construct(
        StarmapUiFactoryInterface $starmapUiFactory,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        EncodedMapInterface $encodedMap,
        UserInterface $user,
        LayerInterface $layer,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ) {
        parent::__construct(
            $mapRepository,
            $starSystemMapRepository,
            $encodedMap,
            $layer,
            $cury,
            $minx,
            $maxx,
            $systemId
        );
        $this->user = $user;
        $this->mapRepository = $mapRepository;
        $this->starmapUiFactory = $starmapUiFactory;
    }

    /**
     * @return array<ExplorableStarMapItemInterface>
     */
    public function getFields(): array
    {
        $layer = $this->layer;
        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }

        $maps = $this->mapRepository->getExplored(
            $this->user->getId(),
            $layer->getId(),
            $this->minx,
            $this->maxx,
            $this->row
        );
        $hasExploredLayer = $this->user->hasExplored($layer->getId());

        $result = [];

        foreach ($maps as $item) {

            $starmapItem = $this->starmapUiFactory->createExplorableStarmapItem($item, $layer);
            if (!$hasExploredLayer && $item->getUserId() === null) {
                $starmapItem->setHide(true);
            }

            $result[$item->getCx()] = $starmapItem;
        }

        return $result;
    }
}
