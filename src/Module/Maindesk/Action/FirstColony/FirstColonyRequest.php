<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\FirstColony;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class FirstColonyRequest implements FirstColonyRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getPlanetId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
