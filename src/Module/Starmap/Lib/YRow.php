<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\Module\Admin\View\Map\EditSection\MapItem;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class YRow
{
    /** @var null|array<MapItem>|array<MapData> */
    protected $fields;

    public function __construct(private MapRepositoryInterface $mapRepository, private StarSystemMapRepositoryInterface $systemMapRepository, private EncodedMapInterface $encodedMap, protected ?LayerInterface $layer, protected int $row, protected int $minx, protected int $maxx, protected int|StarSystemInterface $system)
    {
    }

    /**
     * @return array<MapItem>|array<MapData>
     */
    public function getFields(): array
    {
        if ($this->fields === null) {
            $this->fields = [];
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $layer = $this->layer;
                if ($layer === null) {
                    throw new RuntimeException('this should not happen');
                }

                $map = $this->mapRepository->getByCoordinates(
                    $layer,
                    $i,
                    $this->row
                );

                if ($map !== null) {
                    $this->fields[] = new MapItem(
                        $this->encodedMap,
                        $map
                    );
                }
            }
        }
        return $this->fields;
    }

    /**
     * @return array<MapItem>|array<MapData>
     */
    public function getSystemFields(): array
    {
        if ($this->fields === null) {
            $this->fields = [];

            if ($this->system instanceof StarSystemInterface) {
                $this->fields = array_map(
                    fn (StarSystemMapInterface $systemMap): MapData => $this->mapSystemMapToMapData($systemMap),
                    array_filter(
                        $this->system->getFields()->toArray(),
                        fn (StarSystemMapInterface $systemMap): bool => $systemMap->getSy() === $this->row
                    )
                );
            } else {

                for ($i = $this->minx; $i <= $this->maxx; $i++) {
                    $systemMap = $this->systemMapRepository->getByCoordinates(
                        $this->system,
                        $i,
                        $this->row
                    );
                    if ($systemMap !== null) {
                        $this->fields[] = $this->mapSystemMapToMapData($systemMap);
                    }
                }
            }
        }
        return $this->fields;
    }

    private function mapSystemMapToMapData(StarSystemMapInterface $systemMap): MapData
    {
        return new MapData(
            $systemMap->getSx(),
            $systemMap->getSy(),
            $systemMap->getFieldId()
        );
    }

    /**
     * @return int
     */
    public function getRow(): int
    {
        return $this->row;
    }
}
