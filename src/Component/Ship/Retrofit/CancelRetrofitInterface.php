<?php

namespace Stu\Component\Ship\Retrofit;

use Stu\Orm\Entity\ShipInterface;

interface CancelRetrofitInterface
{
    public function cancelRetrofit(ShipInterface $ship): bool;
}