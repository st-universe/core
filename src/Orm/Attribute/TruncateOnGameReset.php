<?php

declare(strict_types=1);

namespace Stu\Orm\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TruncateOnGameReset
{

    /**
     * @param int $priority higher priority gets truncated first
     */
    public function __construct(
        public readonly int $priority = 1
    ) {}
}
