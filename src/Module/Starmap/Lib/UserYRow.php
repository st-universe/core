<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class UserYRow extends YRow
{
    use LayerExplorationTrait;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        private StarmapUiFactoryInterface $starmapUiFactory,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        EncodedMapInterface $encodedMap,
        private User $user,
        Layer $layer,
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
        $this->mapRepository = $mapRepository;
    }

    /**
     * @return array<ExplorableStarMapItemInterface>
     */
    #[\Override]
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
        $hasExploredLayer = $this->hasExplored($this->user, $layer);

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
