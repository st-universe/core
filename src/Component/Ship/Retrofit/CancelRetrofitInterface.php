<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Retrofit;

use Stu\Orm\Entity\Ship;

interface CancelRetrofitInterface
{
    public function cancelRetrofit(Ship $ship): bool;
}
