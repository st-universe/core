<?php

declare(strict_types=1);

use Stu\Module\Starmap\Lib\ExploreableStarMap;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

class UserYRow extends YRow
{
    /** @var UserInterface */
    private $user;

    /**
     * @param int $layerId
     * @param int $cury
     * @param int $minx
     * @param int $maxx
     * @param int $systemId
     */
    function __construct(UserInterface $user, $layerId, $cury, $minx, $maxx, $systemId = 0)
    {
        parent::__construct($layerId, $cury, $minx, $maxx, $systemId);
        $this->user = $user;
    }

    /**
     * @return array<MapInterface>
     */
    function getFields()
    {
        if ($this->fields === null) {
            // @todo refactor
            global $container;

            $this->fields = [];

            /**
             * @var MapRepositoryInterface
             */
            $repo = $container->get(MapRepositoryInterface::class);
            $result = $repo->getExplored(
                $this->user->getId(),
                $this->layerId,
                (int) $this->minx,
                (int) $this->maxx,
                (int) $this->row
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
