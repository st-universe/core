<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Station;
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
        private SpacecraftShutdownInterface $spacecraftShutdown,
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

        $this->spacecraftShutdown->shutdown($destroyedSpacecraftWrapper, true);
        $spacecraft->getCondition()->setIsDestroyed(true);

        // create trumfield entity
        $trumfield = $this->trumfieldRepository->prototype();
        $trumfield->setFormerRumpId($spacecraft->getRump()->getId());
        $trumfield->setHull((int) ceil($spacecraft->getMaxHull() / 20));
        $trumfield->setLocation($spacecraft->getLocation());
        $this->trumfieldRepository->save($trumfield);

        foreach ($spacecraft->getBeamableStorage() as $storage) {
            $storage->setEntity(null)
                ->setTrumfield($trumfield)
                ->setUser($nobody);

            $this->storageRepository->save($storage);
            $spacecraft->getStorage()->removeElement($storage);
        }
        foreach ($spacecraft->getStorage() as $storage) {
            $this->storageRepository->delete($storage);
        }

        $this->spacecraftStateChanger->changeState($destroyedSpacecraftWrapper, SpacecraftStateEnum::DESTROYED);

        // delete ship systems
        foreach ($spacecraft->getSystems() as $system) {
            $this->shipSystemRepository->delete($system);
        }
        $spacecraft->getSystems()->clear();

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($destroyedSpacecraftWrapper);

        if (!$spacecraft instanceof Station) {
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
