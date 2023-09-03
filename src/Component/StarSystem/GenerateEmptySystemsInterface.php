<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

interface GenerateEmptySystemsInterface
{
    public function generate(int $layerId): int;
}
