<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateLicence;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateLicenceRequest implements CreateLicenceRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->bodyParameter('tradepost')->int()->required();
    }

    public function getWantedLicenceGoodId(): int
    {
        return $this->bodyParameter('wlgid')->int()->required();
    }

    public function getWantedLicenceAmount(): int
    {
        return $this->bodyParameter('wlcount')->int()->required();
    }

    public function getLicenceDays(): int
    {
        return $this->bodyParameter('ldays')->int()->required();
    }
}