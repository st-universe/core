<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MovePm;

interface MovePmRequestInterface
{
    public function getPmId(): int;

    public function getDestinationCategoryId(): int;
}
