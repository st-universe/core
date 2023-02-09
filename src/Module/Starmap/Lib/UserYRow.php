<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class UserYRow extends YRow
{
    private UserInterface $user;

    private MapRepositoryInterface $mapRepository;

    function __construct(
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
    }

    /**
     * @return array<MapInterface|null>|array<StarSystemMapInterface|null>|array<ExploreableStarMapInterface|null>
     */
    function getFields()
    {
        if ($this->fields === null) {
            $this->fields = [];

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
                if (!$hasExploredLayer && $item->getUserId() === null) {
                    $item->setHide(true);
                }
                $this->fields[$item->getCx()] = $item;
            }
        }
        return $this->fields;
    }
}
