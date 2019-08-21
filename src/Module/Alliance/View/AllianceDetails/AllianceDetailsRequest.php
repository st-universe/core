<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\AllianceDetails;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AllianceDetailsRequest implements AllianceDetailsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getAllianceId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

}