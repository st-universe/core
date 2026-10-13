<?php

declare(strict_types=1);

namespace Stu\Component\Asset;

interface AssetVersioningInterface
{
    public function rewrite(string $html): string;
}
