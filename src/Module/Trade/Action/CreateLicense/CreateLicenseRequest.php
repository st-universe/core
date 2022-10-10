<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateLicense;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateLicenseRequest implements CreateLicenseRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->bodyParameter('tradepost')->int()->required();
    }

    public function getWantedLicenseCommodityId(): int
    {
        return $this->bodyParameter('wlgid')->int()->defaultsTo(0);
    }

    public function getWantedLicenseAmount(): int
    {
        return $this->bodyParameter('wlcount')->int()->defaultsTo(0);
    }

    public function getLicenseDays(): int
    {
        return $this->bodyParameter('ldays')->int()->defaultsTo(0);
    }
}
