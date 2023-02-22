<?php

declare(strict_types=1);

namespace Stu\Component\History;

use Stu\Component\History\Event\HistoryEntrySubscriber;
use function DI\autowire;

return [
    HistoryEntrySubscriber::class => autowire(),
];
