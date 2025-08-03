<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\LssBlockade;

use InvalidArgumentException;
use OutOfBoundsException;

class LssBlockadeGrid
{
    private int   $minX;
    private int   $minY;
    private int   $width;
    private int   $height;
    private int   $obsX;
    private int   $obsY;

    /** @var bool[][]  $blocked[$xIdx][$yIdx]   */
    private array $blocked  = [];

    public function __construct(
        int $minX,
        int $maxX,
        int $minY,
        int $maxY,
        int $obsX,
        int $obsY
    ) {
        if ($minX > $maxX || $minY > $maxY) {
            throw new InvalidArgumentException('min‑Koordinaten müssen ≤ max sein.');
        }
        if ($obsX < $minX || $obsX > $maxX || $obsY < $minY || $obsY > $maxY) {
            throw new InvalidArgumentException('Observer liegt nicht im angegebenen Rechteck.');
        }

        $this->minX  = $minX;
        $this->minY  = $minY;
        $this->width  = $maxX - $minX + 1;
        $this->height = $maxY - $minY + 1;
        $this->obsX  = $obsX;
        $this->obsY  = $obsY;
    }

    private function idxX(int $worldX): int
    {
        return $worldX - $this->minX;
    }
    private function idxY(int $worldY): int
    {
        return $worldY - $this->minY;
    }

    private function inRange(int $x, int $y): bool
    {
        return $x >= $this->minX && $x < $this->minX + $this->width
            && $y >= $this->minY && $y < $this->minY + $this->height;
    }

    public function setBlocked(int $x, int $y): void
    {
        if (!$this->inRange($x, $y)) {
            throw new OutOfBoundsException("Koordinate ($x,$y) liegt außerhalb des Grids");
        }

        $this->blocked[$this->idxX($x)][$this->idxY($y)] = true;
    }

    public function isVisible(int $x, int $y): bool
    {
        if (!$this->inRange($x, $y)) {
            return false;
        }

        $ixT = $this->idxX($x);
        $iyT = $this->idxY($y);
        if (!empty($this->blocked[$ixT][$iyT])) {
            return false;
        }

        $dx = $x - $this->obsX;
        $dy = $y - $this->obsY;
        $sx = $dx >= 0 ? 1 : -1;
        $sy = $dy >= 0 ? 1 : -1;
        $dx = abs($dx);
        $dy = abs($dy);

        $err = $dx - $dy;          // Bresenham‑Setup
        $cx  = $this->obsX;
        $cy  = $this->obsY;

        while ($cx !== $x || $cy !== $y) {

            $e2 = $err << 1;       //  = 2*err
            $nx = $cx;
            $ny = $cy;

            if ($e2 > -$dy) {      // Schritt in x‑Richtung
                $err -= $dy;
                $nx  += $sx;
            }
            if ($e2 <  $dx) {      // Schritt in y‑Richtung
                $err += $dx;
                $ny  += $sy;
            }

            if ($cx !== $nx && $cy !== $ny) {
                $side1Blocked = !empty($this->blocked[$this->idxX($cx)][$this->idxY($ny)]);
                $side2Blocked = !empty($this->blocked[$this->idxX($nx)][$this->idxY($cy)]);
                if ($side1Blocked || $side2Blocked) {
                    return false;
                }
            }

            if (!empty($this->blocked[$this->idxX($nx)][$this->idxY($ny)])) {
                return false;
            }

            $cx = $nx;
            $cy = $ny;
        }

        return true;
    }
}
