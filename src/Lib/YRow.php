<?php

declare(strict_types=1);

use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class YRow
{

    protected $layerId = null;
    protected $row = null;
    protected $minx = null;
    protected $maxx = null;
    protected $systemId = null;

    function __construct($layerId, $cury, $minx, $maxx, $systemId = 0)
    {
        $this->layerId = $layerId;
        $this->row = $cury;
        $this->minx = $minx;
        $this->maxx = $maxx;
        $this->systemId = $systemId;
    }

    protected $fields = null;

    function getFields()
    {
        if ($this->fields === null) {
            // @todo refactor
            global $container;

            /**
             * @var MapRepositoryInterface
             */
            $mapRepository = $container->get(MapRepositoryInterface::class);
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $this->fields[] = $mapRepository->getByCoordinates(
                    $this->layerId,
                    (int) $i,
                    (int) $this->row
                );
            }
        }
        return $this->fields;
    }

    function getSystemFields()
    {
        if ($this->fields === null) {
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                // @todo refactor
                global $container;

                $this->fields[] = $container->get(StarSystemMapRepositoryInterface::class)->getByCoordinates(
                    (int) $this->systemId,
                    (int) $i,
                    (int) $this->row
                );
            }
        }
        return $this->fields;
    }

    function getRow()
    {
        return $this->row;
    }
}
