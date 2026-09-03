<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchiveComments;

interface ShowKnArchiveCommentsRequestInterface
{
    public function getKnPostId(): int;
}
