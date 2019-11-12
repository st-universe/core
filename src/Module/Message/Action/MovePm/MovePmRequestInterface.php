<?php

namespace Stu\Module\Message\Action\MovePm;

interface MovePmRequestInterface
{
    public function getCategoryId(): int;

    public function getPmId(): int;

    public function getDestinationCategoryId(): int;
}
