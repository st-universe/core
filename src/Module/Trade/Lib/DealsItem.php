<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\DealsInterface;

final class DealsItem implements DealsItemInterface
{
    private DealsInterface $deals;


    public function __construct(
        DealsInterface $deals
    ) {
        $this->deals = $deals;
    }

    public function getDeals(): DealsInterface
    {
        return $this->deals;
    }
}