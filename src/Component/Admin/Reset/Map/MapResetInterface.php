<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Map;

interface MapResetInterface
{

    public function deleteAllFlightSignatures(): void;

    public function deleteAllUserMaps(): void;
}
