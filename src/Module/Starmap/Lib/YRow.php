<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use RuntimeException;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class YRow
{
    protected ?LayerInterface $layer;

    protected int $row;

    protected int $minx;

    protected int $maxx;

    protected int|StarSystemInterface $system;

    /** @var null|array<MapInterface|null>|array<StarSystemMapInterface|null> */
    protected $fields;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $systemMapRepository;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $systemMapRepository,
        ?LayerInterface $layer,
        int $cury,
        int $minx,
        int $maxx,
        int|StarSystemInterface $system
    ) {
        $this->layer = $layer;
        $this->row = $cury;
        $this->minx = $minx;
        $this->maxx = $maxx;
        $this->system = $system;
        $this->mapRepository = $mapRepository;
        $this->systemMapRepository = $systemMapRepository;
    }

    /**
     * @return array<StarSystemMapInterface|null>|array<MapInterface|null>
     */
    public function getFields(): iterable
    {
        if ($this->fields === null) {
            $this->fields = [];
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $layer = $this->layer;
                if ($layer === null) {
                    throw new RuntimeException('this should not happen');
                }

                $this->fields[] = $this->mapRepository->getByCoordinates(
                    $layer->getId(),
                    $i,
                    $this->row
                );
            }
        }
        return $this->fields;
    }

    /**
     * @return array<StarSystemMapInterface|null>|array<MapInterface|null>
     */
    public function getSystemFields()
    {
        if ($this->fields === null) {
            $this->fields = [];

            if ($this->system instanceof StarSystemInterface) {
                $this->fields = array_filter(
                    $this->system->getFields()->toArray(),
                    fn (StarSystemMapInterface $systemMap) => $systemMap->getSy() === $this->row
                );
            } else {

                for ($i = $this->minx; $i <= $this->maxx; $i++) {
                    $this->fields[] = $this->systemMapRepository->getByCoordinates(
                        $this->system,
                        $i,
                        $this->row
                    );
                }
            }
        }
        return $this->fields;
    }

    /**
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }
}
