<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\History\HistoryTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class BorgAssimilationCompletion
{
    public function __construct(
        private BorgAssimilationRepositoryInterface $repository,
        private FleetRepositoryInterface $fleets,
        private StorageRepositoryInterface $storages,
        private EntryCreatorInterface $history,
        private PrivateMessageSenderInterface $messages,
        private BorgSphereEnergy $energy,
        private BorgSphereTorpedoSupply $torpedoes
    ) {}

    public function complete(SpacecraftAssimilated $entry, int $now): void
    {
        $ship = $entry->getSpacecraft();
        $source = $entry->getSource();
        if (!$ship instanceof Ship || !$source instanceof Ship || !$entry->isActive()) {
            return;
        }
        $owner = $ship->getUser();
        $text = sprintf('Das Schiff %s von Spieler %s wurde in Sektor %s von der %s assimiliert', $ship->getName(), $owner->getName(), $ship->getSectorString(), $source->getName());
        $source->setTractoredShip(null);
        $source->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        $ship->transferToUser($source->getUser());
        foreach ($ship->getCrewAssignments() as $assignment) {
            $assignment->setUser($source->getUser());
            $assignment->getCrew()->transferToUser($source->getUser());
        }
        $this->changeStorageOwner($ship);
        $entry->complete($now);
        $ship->setFleet($this->fleet($source))->setIsFleetLeader(false);
        $this->repair($ship);
        $this->history->createEntry(HistoryTypeEnum::SHIP, $text, $source->getUser()->getId(), $owner->getId(), $ship->getLocation());
        $this->messages->send($source->getUser()->getId(), $owner->getId(), $text, PrivateMessageFolderTypeEnum::SPECIAL_SHIP);
        $this->repository->save($entry);
    }

    private function fleet(Ship $source): Fleet
    {
        $fleet = $source->getFleet();
        if ($fleet === null) {
            $fleet = $this->fleets->prototype()->setUser($source->getUser())->setName($source->getName())->setLeadShip($source);
            $source->setFleet($fleet)->setIsFleetLeader(true);
            $this->fleets->save($fleet);
        }
        return $fleet;
    }

    private function changeStorageOwner(Ship $ship): void
    {
        foreach ($ship->getStorage() as $storage) {
            if ($storage->getCommodity()->isBoundToAccount()) {
                $ship->getStorage()->removeElement($storage);
                $this->storages->delete($storage);
            } else {
                $storage->transferToUser($ship->getUser());
            }
        }
        foreach ($ship->getTorpedoStorages() as $storage) {
            $storage->getStorage()->transferToUser($ship->getUser());
        }
    }

    private function repair(Ship $ship): void
    {
        $ship->getCondition()->setHull($ship->getMaxHull())->setShield($ship->getMaxShield(true))->setDisabled(false)->setState(SpacecraftStateEnum::NONE);
        foreach ($ship->getSystems() as $system) {
            $system->setStatus(100);
            if (in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::IMPULSEDRIVE, SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::LIFE_SUPPORT], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_ON);
            }
        }
        $this->energy->recharge($ship);
        $this->torpedoes->refill($ship);
    }
}
