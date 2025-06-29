<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\SpacecraftRump;

class SignatureWrapper
{
    public function __construct(private FlightSignature $signature) {}

    public function getRump(): ?SpacecraftRump
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

    public function getShipName(): ?string
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

    public function getAge(): int
    {
        return time() - $this->signature->getTime();
    }
}
