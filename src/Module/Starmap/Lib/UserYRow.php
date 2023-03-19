<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Generator;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
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
        UserInterface $user,
        int $layerId,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ) {
        parent::__construct(
            $mapRepository,
            $starSystemMapRepository,
            $layerId,
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
     * @return Generator<MapInterface|null>|Generator<StarSystemMapInterface|null>|Generator<ExplorableStarMapItemInterface|null>
     */
    public function getFields(): Generator
    {
        $result = $this->mapRepository->getExplored(
            $this->user->getId(),
            $this->layerId,
            $this->minx,
            $this->maxx,
            $this->row
        );
        $hasExploredLayer = $this->user->hasExplored($this->layerId);

        /** @var ExploreableStarMap $item */
        foreach ($result as $item) {
            $starmapItem = $this->starmapUiFactory->createExplorableStarmapItem($item);
            if (!$hasExploredLayer && $item->getUserId() === null) {
                $starmapItem->setHide(true);
            }

            yield $item->getCx() => $starmapItem;
        }
    }
}
