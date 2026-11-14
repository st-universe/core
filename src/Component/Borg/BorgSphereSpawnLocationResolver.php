<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class BorgSphereSpawnLocationResolver
{
    public function __construct(private LayerRepositoryInterface $layers, private MapRepositoryInterface $maps) {}

    public function resolve(string|false $layerInput, string|false $xInput, string|false $yInput, InformationInterface $information): Map|false|null
    {
        $layerInput = $this->normalize($layerInput);
        $xInput = $this->normalize($xInput);
        $yInput = $this->normalize($yInput);
        if ($layerInput === '' && $xInput === '' && $yInput === '') {
            return null;
        }
        $layerId = $this->positiveInteger($layerInput);
        $x = $this->positiveInteger($xInput);
        $y = $this->positiveInteger($yInput);
        if ($layerId === null || $x === null || $y === null) {
            $information->addInformation('Für einen eigenen Spawnort müssen Layer-ID, X und Y vollständig als positive ganze Zahlen angegeben werden');
            return false;
        }
        $layer = $this->layers->find($layerId);
        if ($layer === null) {
            $information->addInformation('Der angegebene Layer existiert nicht');
            return false;
        }
        $location = $this->maps->getByCoordinates($layer, $x, $y);
        if ($location === null) {
            $information->addInformation('An den angegebenen Koordinaten existiert kein Kartenfeld');
            return false;
        }
        return $location;
    }

    private function normalize(string|false $value): string
    {
        return trim($value === false ? '' : $value);
    }

    private function positiveInteger(string $value): ?int
    {
        if (!ctype_digit($value)) {
            return null;
        }
        $number = filter_var(ltrim($value, '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $number === false ? null : $number;
    }
}
