<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\Commodity;

interface BasicTradeItemInterface
{
    public function getUniqId(): string;

    public function getCommodity(): Commodity;

    public function getStoredAmount(): int;

    public function getBuyValue(): int;

    public function getSellValue(): int;
}
