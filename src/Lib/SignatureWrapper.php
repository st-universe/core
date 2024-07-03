<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Module\Tal\TalHelper;

class SignatureWrapper
{
    private $signature;

    public function __construct($signature)
    {
        $this->signature = $signature;
    }

    public function getRump()
    {
        if ($this->signature->isCloaked()) {
            if ($this->signature->getTime() > (time() - FlightSignatureVisibilityEnum::RUMP_VISIBILITY_CLOAKED)) {
                return $this->signature->getRump();
            } else {
                return null;
            }
        }
        if ($this->signature->getTime() > (time() - FlightSignatureVisibilityEnum::RUMP_VISIBILITY_UNCLOAKED)) {
            return $this->signature->getRump();
        } else {
            return null;
        }
    }

    public function getShipName()
    {
        if ($this->signature->isCloaked()) {
            if ($this->signature->getTime() > (time() - FlightSignatureVisibilityEnum::NAME_VISIBILITY_CLOAKED)) {
                return $this->signature->getShipName();
            } else {
                return null;
            }
        }
        if ($this->signature->getTime() > (time() - FlightSignatureVisibilityEnum::NAME_VISIBILITY_UNCLOAKED)) {
            return $this->signature->getShipName();
        } else {
            return null;
        }
    }

    public function getAge(): string
    {
        return TalHelper::formatSeconds((string) (time() - $this->signature->getTime()));
    }
}
