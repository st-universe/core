<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\FirstColony;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class FirstColonyRequest implements FirstColonyRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlanetId(): int {
        return $this->queryParameter('id')->int()->required();
    }

}