<?php

namespace Stu\Component\Ship\Retrofit;

use Stu\Orm\Entity\Ship;

interface CancelRetrofitInterface
{
    public function cancelRetrofit(Ship $ship): bool;
}
