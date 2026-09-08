<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Lib;

final readonly class CrewRaceSubmissionData
{
    public function __construct(
        public string $description,
        public int $maleRatio,
        public string $gfxPath,
        public int $chance,
        public bool $shared,
        public bool $civil
    ) {}
}
