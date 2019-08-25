<?php

namespace Stu\Module\Communication\Action\MovePm;

interface MovePmRequestInterface
{
    public function getCategoryId(): int;

    public function getPmId(): int;

    public function getDestinationCategoryId(): int;
}