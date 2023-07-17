<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Map;

interface MapResetInterface
{
    public function deleteAllFlightSignatures(): void;

    public function deleteAllUserMaps(): void;

    public function deleteAllAstroEntries(): void;

    public function deleteAllColonyScans(): void;

    public function deleteAllTachyonScans(): void;

    public function deleteAllUserLayers(): void;
}
