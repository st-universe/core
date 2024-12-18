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
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TrumfieldRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class TransformToTrumfield implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private TrumfieldRepositoryInterface $trumfieldRepository,
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private StorageRepositoryInterface $storageRepository,
        private UserRepositoryInterface $userRepository,
        private ShipShutdownInterface $shipShutdown,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ClearTorpedoInterface $clearTorpedo,
        private ConstructionProgressRepositoryInterface $constructionProgressRepository,
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $nobody = $this->userRepository->getFallbackUser();
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
            $storage->setSpacecraft(null)
                ->setTrumfield($trumfield)
                ->setUser($nobody);

            $this->storageRepository->save($storage);
            $spacecraft->getStorage()->removeElement($storage);
        }
        foreach ($spacecraft->getStorage() as $storage) {
            $this->storageRepository->delete($storage);
        }

        $this->spacecraftStateChanger->changeShipState($destroyedSpacecraftWrapper, SpacecraftStateEnum::SHIP_STATE_DESTROYED);

        // delete ship systems
        foreach ($spacecraft->getSystems() as $system) {
            $this->shipSystemRepository->delete($system);
        }
        $spacecraft->getSystems()->clear();

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($destroyedSpacecraftWrapper);

        if (!$spacecraft instanceof StationInterface) {
            return;
        }

        $influenceArea = $spacecraft->getInfluenceArea();
        if ($influenceArea !== null) {
            $influenceArea->unsetStation();
        }

        $constructionProgress = $spacecraft->getConstructionProgress();
        if ($constructionProgress !== null) {
            $this->constructionProgressRepository->delete($constructionProgress);
        }
    }
}
