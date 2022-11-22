<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\DealsInterface;

interface DealsItemInterface
{
    public function getDeals(): DealsInterface;
}