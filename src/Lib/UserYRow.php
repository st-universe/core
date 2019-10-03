<?php

declare(strict_types=1);

use Stu\Component\Map\MapEnum;
use Stu\Module\Starmap\Lib\ExploreableStarMap;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

class UserYRow extends YRow
{
    private $user;

    function __construct(UserInterface $user, $cury, $minx, $maxx, $systemId = 0)
    {
        parent::__construct($cury, $minx, $maxx, $systemId);
        $this->user = $user;
    }

    function getFields()
    {
        if ($this->fields === null) {
            // @todo refactor
            global $container;

            $this->fields = [];

            $result = $container->get(MapRepositoryInterface::class)->getExplored($this->user->getId(), (int) $this->minx, (int) $this->maxx, (int) $this->row);
            $mapType = $this->user->getMapType();

            /** @var ExploreableStarMap $item */
            foreach ($result as $item) {
                if ($mapType == MapEnum::MAPTYPE_INSERT) {
                    if ($item->getUserId() === null) {
                        $item->setHide(true);
                    }
                } else {
                    if ($item->getUserId() !== null) {
                        $item->setHide(true);
                    }
                }
                $this->fields[$item->getCx()] = $item;
            }
        }
        return $this->fields;
    }
}
