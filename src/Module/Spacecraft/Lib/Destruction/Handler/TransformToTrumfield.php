<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TrumfieldRepositoryInterface;

class TransformToTrumfield implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private TrumfieldRepositoryInterface $trumfieldRepository,
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private StorageRepositoryInterface $storageRepository,
        private ShipShutdownInterface $shipShutdown,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ClearTorpedoInterface $clearTorpedo
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $spacecraft = $destroyedSpacecraftWrapper->get();

        $this->shipShutdown->shutdown($destroyedSpacecraftWrapper, true);
        $spacecraft->setIsDestroyed(true);

        // create trumfield entity
        $trumfield = $this->trumfieldRepository->prototype();
        $trumfield->setFormerRumpId($spacecraft->getRump()->getId());
        $trumfield->setHuell((int) ceil($spacecraft->getMaxHull() / 20));
        $trumfield->setLocation($spacecraft->getLocation());
        $this->trumfieldRepository->save($trumfield);

        foreach ($spacecraft->getBeamableStorage() as $storage) {
            $storage->setSpacecraft(null);
            $storage->setTrumfield($trumfield);
            $this->storageRepository->save($storage);
        }

        $this->spacecraftStateChanger->changeShipState($destroyedSpacecraftWrapper, SpacecraftStateEnum::SHIP_STATE_DESTROYED);

        // delete ship systems
        $this->shipSystemRepository->truncateByShip($spacecraft->getId());
        $spacecraft->getSystems()->clear();

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($destroyedSpacecraftWrapper);

        $this->spacecraftRepository->delete($spacecraft);
    }
}
