<?php

namespace Stu\Module\Trade\Action\CreateLicence;

interface CreateLicenceRequestInterface
{
    
    public function getTradePostId(): int;
    
    public function getWantedLicenceGoodId(): int;

    public function getWantedLicenceAmount(): int;

    public function getLicenceDays(): int;

}