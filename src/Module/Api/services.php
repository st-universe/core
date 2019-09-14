<?php

declare(strict_types=1);

namespace Stu\Module\Colony;

use Stu\Module\Api\V1\News\GetNews;
use function DI\autowire;

return [
    GetNews::class => autowire(GetNews::class),
];