<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Map\VisualPanel\LssBlockade\LssBlockadeGrid;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\MapRepositoryInterface;

final class StarmapSensorCoverage
{
    public function __construct(private MapRepositoryInterface $mapRepository) {}

    /**
     * @param array<int, array<string, mixed>> $ranges
     * @return array{
     *     runs: array<int, array{y: int, startX: int, endX: int}>,
     *     tachyonRuns: array<int, array{y: int, startX: int, endX: int}>,
     *     sourceCount: int,
     *     fieldCount: int,
     *     tachyonFieldCount: int,
     *     visibleKeys: array<string, true>,
     *     tachyonKeys: array<string, true>
     * }
     */
    public function buildSensorCoverage(Layer $layer, array $ranges): array
    {
        $visibleKeys = [];
        $tachyonKeys = [];

        if ($ranges === []) {
            return $this->buildCoverageResult($visibleKeys, $tachyonKeys, 0);
        }

        $blockers = $this->getLssBlockadeKeys($layer, $ranges);

        foreach ($ranges as $range) {
            $sourceX = (int) $range['x'];
            $sourceY = (int) $range['y'];
            $sensorRange = max(0, (int) $range['sensor_range']);
            $tachyonRange = $this->getEffectiveTachyonRange($sensorRange, max(
                0,
                (int) ($range['tachyon_range'] ?? 0)
            ));
            $maxRange = $sensorRange;

            if ($maxRange <= 0) {
                continue;
            }

            $minX = max(1, $sourceX - $maxRange);
            $maxX = min($layer->getWidth(), $sourceX + $maxRange);
            $minY = max(1, $sourceY - $maxRange);
            $maxY = min($layer->getHeight(), $sourceY + $maxRange);

            $grid = new LssBlockadeGrid($minX, $maxX, $minY, $maxY, $sourceX, $sourceY);
            foreach ($blockers as $blockerKey => $_value) {
                [$blockerX, $blockerY] = array_map('intval', explode(':', $blockerKey));
                if ($blockerX < $minX || $blockerX > $maxX || $blockerY < $minY || $blockerY > $maxY) {
                    continue;
                }
                $grid->setBlocked($blockerX, $blockerY);
            }

            for ($y = $minY; $y <= $maxY; $y++) {
                for ($x = $minX; $x <= $maxX; $x++) {
                    if (!$grid->isVisible($x, $y)) {
                        continue;
                    }

                    $distanceX = abs($x - $sourceX);
                    $distanceY = abs($y - $sourceY);
                    $key = $this->fieldKey($x, $y);

                    if ($distanceX <= $sensorRange && $distanceY <= $sensorRange) {
                        $visibleKeys[$key] = true;
                    }
                    if ($tachyonRange > 0 && $distanceX <= $tachyonRange && $distanceY <= $tachyonRange) {
                        $tachyonKeys[$key] = true;
                    }
                }
            }
        }

        return $this->buildCoverageResult($visibleKeys, $tachyonKeys, count($ranges));
    }

    /**
     * @param array<int, array<string, mixed>> $ranges
     * @return array<string, true>
     */
    private function getLssBlockadeKeys(Layer $layer, array $ranges): array
    {
        $minX = $layer->getWidth();
        $maxX = 1;
        $minY = $layer->getHeight();
        $maxY = 1;

        foreach ($ranges as $range) {
            $sourceX = (int) $range['x'];
            $sourceY = (int) $range['y'];
            $maxRange = max(0, (int) $range['sensor_range']);
            if ($maxRange <= 0) {
                continue;
            }

            $minX = min($minX, max(1, $sourceX - $maxRange));
            $maxX = max($maxX, min($layer->getWidth(), $sourceX + $maxRange));
            $minY = min($minY, max(1, $sourceY - $maxRange));
            $maxY = max($maxY, min($layer->getHeight(), $sourceY + $maxRange));
        }

        if ($minX > $maxX || $minY > $maxY) {
            return [];
        }

        $boundaries = new PanelBoundaries($minX, $maxX, $minY, $maxY, $layer);
        $keys = [];
        foreach ($this->mapRepository->getLssBlockadeLocations($boundaries) as $entry) {
            $effects = $entry['effects'];
            if (!is_string($effects) || !str_contains($effects, FieldTypeEffectEnum::LSS_BLOCKADE->value)) {
                continue;
            }
            $keys[$this->fieldKey((int) $entry['x'], (int) $entry['y'])] = true;
        }

        return $keys;
    }

    private function getEffectiveTachyonRange(int $sensorRange, int $tachyonRange): int
    {
        return min($sensorRange, $tachyonRange);
    }

    /**
     * @param array<string, true> $visibleKeys
     * @param array<string, true> $tachyonKeys
     * @return array{
     *     runs: array<int, array{y: int, startX: int, endX: int}>,
     *     tachyonRuns: array<int, array{y: int, startX: int, endX: int}>,
     *     sourceCount: int,
     *     fieldCount: int,
     *     tachyonFieldCount: int,
     *     visibleKeys: array<string, true>,
     *     tachyonKeys: array<string, true>
     * }
     */
    private function buildCoverageResult(array $visibleKeys, array $tachyonKeys, int $sourceCount): array
    {
        return [
            'runs' => $this->buildRunsFromKeys($visibleKeys),
            'tachyonRuns' => $this->buildRunsFromKeys($tachyonKeys),
            'sourceCount' => $sourceCount,
            'fieldCount' => count($visibleKeys),
            'tachyonFieldCount' => count($tachyonKeys),
            'visibleKeys' => $visibleKeys,
            'tachyonKeys' => $tachyonKeys
        ];
    }

    /**
     * @param array<string, true> $keys
     * @return array<int, array{y: int, startX: int, endX: int}>
     */
    private function buildRunsFromKeys(array $keys): array
    {
        $rows = [];
        foreach (array_keys($keys) as $key) {
            [$x, $y] = array_map('intval', explode(':', $key));
            $rows[$y][] = $x;
        }
        ksort($rows);

        $runs = [];
        foreach ($rows as $y => $xs) {
            sort($xs);
            $startX = $xs[0];
            $lastX = $startX;
            foreach (array_slice($xs, 1) as $x) {
                if ($x === ($lastX + 1)) {
                    $lastX = $x;
                    continue;
                }
                $runs[] = ['y' => $y, 'startX' => $startX, 'endX' => $lastX];
                $startX = $lastX = $x;
            }
            $runs[] = ['y' => $y, 'startX' => $startX, 'endX' => $lastX];
        }

        return $runs;
    }

    private function fieldKey(int $x, int $y): string
    {
        return sprintf('%d:%d', $x, $y);
    }
}
