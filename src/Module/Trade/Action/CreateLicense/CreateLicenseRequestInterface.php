<?php

namespace Stu\Module\Trade\Action\CreateLicense;

interface CreateLicenseRequestInterface
{

    public function getTradePostId(): int;

    public function getWantedLicenseGoodId(): int;

    public function getWantedLicenseAmount(): int;

    public function getLicenseDays(): int;
}
