<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipRetrofit;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShipRetrofitRequest implements ShowShipRetrofitRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getColonyId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
