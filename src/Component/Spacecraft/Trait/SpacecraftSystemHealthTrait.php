<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

trait SpacecraftSystemHealthTrait
{
    use SpacecraftTrait;
    use SpacecraftSystemExistenceTrait;

    public function isSystemHealthy(SpacecraftSystemTypeEnum $type): bool
    {
        if (!$this->hasSpacecraftSystem($type)) {
            return false;
        }

        return $this->getSpacecraftSystem($type)->isHealthy();
    }

    public function isDeflectorHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::DEFLECTOR);
    }

    public function isMatrixScannerHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
    }

    public function isTorpedoStorageHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO_STORAGE);
    }

    public function isShuttleRampHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP);
    }

    public function isWebEmitterHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::THOLIAN_WEB);
    }
}
