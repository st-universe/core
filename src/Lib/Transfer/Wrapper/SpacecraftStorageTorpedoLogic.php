<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

class SpacecraftStorageTorpedoLogic
{
    public function canTransferTorpedos(SpacecraftInterface $spacecraft, InformationInterface $information): bool
    {
        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)) {
            $information->addInformation("Das Torpedolager ist zerstört");
            return false;
        }

        return true;
    }

    public function canStoreTorpedoType(SpacecraftInterface $spacecraft, TorpedoTypeInterface $torpedoType, InformationInterface $information): bool
    {
        if (
            !$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            && $spacecraft->getRump()->getTorpedoLevel() !== $torpedoType->getLevel()
        ) {
            $information->addInformationf('Die %s kann den Torpedotyp nicht ausrüsten', $spacecraft->getName());
            return false;
        }

        if (
            !$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            && $torpedoType->getLevel() > $spacecraft->getRump()->getTorpedoLevel()
        ) {
            $information->addInformationf("Die %s kann den Torpedotyp nicht ausrüsten", $spacecraft->getName());
            return false;
        }

        if (
            $spacecraft->getTorpedo() !== null
            && $spacecraft->getTorpedo() !== $torpedoType
        ) {
            $information->addInformation("Es ist bereits ein anderer Torpedotyp geladen");
            return false;
        }

        return true;
    }
}
