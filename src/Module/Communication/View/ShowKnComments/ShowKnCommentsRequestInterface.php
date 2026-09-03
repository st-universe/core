<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

interface ShowKnCommentsRequestInterface
{
    public function getKnPostId(): int;
}
