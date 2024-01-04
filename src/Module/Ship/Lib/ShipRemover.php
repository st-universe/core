<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Ship\Lib\Crew\ShipLeaverInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

//TODO use handler pattern for ship destruction & unit tests
final class ShipRemover implements ShipRemoverInterface
{
    private ShipSystemRepositoryInterface $shipSystemRepository;

    private StorageRepositoryInterface $storageRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private CrewRepositoryInterface $crewRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipLeaverInterface $shipLeaver;

    private ClearTorpedoInterface $clearTorpedo;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipStateChangerInterface $shipStateChanger;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    private ShipShutdownInterface $shipShutdown;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        StorageRepositoryInterface $storageRepository,
        ShipStorageManagerInterface $shipStorageManager,
        CrewRepositoryInterface $crewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipLeaverInterface $shipLeaver,
        ClearTorpedoInterface $clearTorpedo,
        TradePostRepositoryInterface $tradePostRepository,
        ShipStateChangerInterface $shipStateChanger,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ShipTakeoverManagerInterface $shipTakeoverManager,
        ShipShutdownInterface $shipShutdown,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->storageRepository = $storageRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->crewRepository = $crewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipLeaver = $shipLeaver;
        $this->clearTorpedo = $clearTorpedo;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipStateChanger = $shipStateChanger;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->shipTakeoverManager = $shipTakeoverManager;
        $this->shipShutdown = $shipShutdown;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function destroy(ShipWrapperInterface $wrapper): ?string
    {
        $trumfieldRump = $this->shipRumpRepository->find(ShipRumpEnum::SHIP_CATEGORY_TRUMFIELD);
        if ($trumfieldRump === null) {
            throw new RuntimeException('trumfield rump missing');
        }

        $msg = null;

        $ship = $wrapper->get();
        $user = $ship->getUser();

        $this->cancelBothTakeover($ship);
        $this->shipShutdown->shutdown($wrapper, true);

        //leave ship if there is crew
        if ($ship->getCrewCount() > 0) {
            $msg = $this->shipLeaver->evacuate($wrapper);
        }

        $this->leaveSomeIntactModules($ship);

        $ship->setFormerRumpId($ship->getRump()->getId());
        $ship->setRump($trumfieldRump);
        $ship->setHuell((int) ceil($ship->getMaxHull() / 20));
        $ship->setUser($this->userRepository->getFallbackUser());
        $ship->setBuildplan(null);
        $ship->setSpacecraftType(SpacecraftTypeEnum::SPACECRAFT_TYPE_OTHER);
        $ship->setShield(0);
        $ship->setAlertStateGreen();
        $ship->setInfluenceArea(null);
        $oldName = $ship->getName();
        $ship->setName(_('Trümmer'));
        $ship->setIsDestroyed(true);
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_DESTROYED);

        // delete ship systems
        $this->shipSystemRepository->truncateByShip($ship->getId());
        $ship->getSystems()->clear();

        if ($user->getState() === UserEnum::USER_STATE_COLONIZATION_SHIP) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);
            $this->userRepository->save($user);
        }

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($wrapper);

        // delete trade post stuff
        if ($ship->getTradePost() !== null) {
            $this->destroyTradepost($ship->getTradePost());
            $ship->setTradePost(null);
        }

        // change storage owner
        $this->orphanizeStorage($ship);

        $this->shipRepository->save($ship);

        // clear tractor status
        $tractoringShipWrapper = $wrapper->getTractoringShipWrapper();
        if ($tractoringShipWrapper !== null) {
            $tractoringShip = $tractoringShipWrapper->get();
            $this->shipSystemManager->deactivate($tractoringShipWrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);

            $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $tractoringShip->getId());

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $tractoringShip->getUser()->getId(),
                sprintf('Die im Traktorstrahl der %s befindliche %s wurde zerstört', $tractoringShip->getName(), $oldName),
                $tractoringShip->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }

        // reset tracker devices
        $this->resetTrackerDevices($ship->getId());

        return $msg;
    }

    private function cancelBothTakeover(ShipInterface $ship): void
    {
        $this->shipTakeoverManager->cancelBothTakeover(
            $ship,
            ', da das Schiff zerstört wurde'
        );
    }

    private function resetTrackerDevices(int $shipId): void
    {
        foreach ($this->shipSystemRepository->getTrackingShipSystems($shipId) as $system) {
            $wrapper = $this->shipWrapperFactory->wrapShip($system->getShip());

            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, true);
        }
    }

    private function leaveSomeIntactModules(ShipInterface $ship): void
    {
        if ($ship->isShuttle()) {
            return;
        }

        $intactModules = [];

        foreach ($ship->getSystems() as $system) {
            if (
                $system->getModule() !== null
                && $system->getStatus() == 100
            ) {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules)) {
                    $intactModules[$module->getId()] = $module;
                }
            }
        }

        //leave 50% of all intact modules
        $leaveCount = (int) ceil(count($intactModules) / 2);
        for ($i = 1; $i <= $leaveCount; $i++) {
            $module = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);

            $this->shipStorageManager->upperStorage(
                $ship,
                $module->getCommodity(),
                1
            );
        }
    }

    private function orphanizeStorage(ShipInterface $ship): void
    {
        foreach ($ship->getStorage() as $storage) {
            $storage->setUser($this->userRepository->getFallbackUser());
            $this->storageRepository->save($storage);
        }
    }

    private function destroyTradepost(TradePostInterface $tradePost): void
    {
        //salvage offers and storage
        $storages = $this->storageRepository->getByTradePost($tradePost->getId());
        foreach ($storages as $storage) {
            //only 50% off all storages
            if (random_int(0, 1) === 0) {
                $this->storageRepository->delete($storage);
                continue;
            }

            //only 0 to 50% of the specific amount
            $amount = (int)ceil($storage->getAmount() / 100 * random_int(0, 50));

            if ($amount === 0) {
                $this->storageRepository->delete($storage);
                continue;
            }

            //add to trumfield storage
            $this->shipStorageManager->upperStorage(
                $tradePost->getShip(),
                $storage->getCommodity(),
                $amount
            );

            $this->storageRepository->delete($storage);
        }

        //remove tradepost and cascading stuff
        $this->tradePostRepository->delete($tradePost);
    }

    public function remove(ShipInterface $ship, ?bool $truncateCrew = false): void
    {
        $wrapper = $this->shipWrapperFactory->wrapShip($ship);

        $this->shipShutdown->shutdown($wrapper, true);
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

        //both sides have to be cleared, foreign key violation
        if ($ship->isTractoring()) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
        } else {
            $tractoringShipWrapper = $wrapper->getTractoringShipWrapper();
            if ($tractoringShipWrapper !== null) {
                $this->shipSystemManager->deactivate($tractoringShipWrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            }
        }

        foreach ($ship->getStorage() as $item) {
            $this->storageRepository->delete($item);
        }

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($wrapper);

        if ($truncateCrew) {
            $crewArray = [];
            foreach ($ship->getCrewAssignments() as $shipCrew) {
                $crewArray[] = $shipCrew->getCrew();
            }

            $this->shipCrewRepository->truncateByShip($ship->getId());

            foreach ($crewArray as $crew) {
                $this->crewRepository->delete($crew);
            }

            $ship->getCrewAssignments()->clear();
        }

        // reset tracker devices
        $this->resetTrackerDevices($ship->getId());

        foreach ($ship->getSystems() as $shipSystem) {
            $this->shipSystemRepository->delete($shipSystem);
        }

        $this->shipRepository->delete($ship);
    }
}
