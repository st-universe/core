<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class TransformToTrumfield implements ShipDestructionHandlerInterface
{
    public function __construct(
        private ShipShutdownInterface $shipShutdown,
        private ShipRumpRepositoryInterface $shipRumpRepository,
        private ShipSystemRepositoryInterface $shipSystemRepository,
        private UserRepositoryInterface $userRepository,
        private ShipStateChangerInterface $shipStateChanger,
        private ClearTorpedoInterface $clearTorpedo
    ) {
    }

    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $ship = $destroyedShipWrapper->get();

        $this->shipShutdown->shutdown($destroyedShipWrapper, true);

        $trumfieldRump = $this->shipRumpRepository->find(ShipRumpEnum::SHIP_CATEGORY_TRUMFIELD);
        if ($trumfieldRump === null) {
            throw new RuntimeException('trumfield rump missing');
        }

        $ship->setFormerRumpId($ship->getRump()->getId());
        $ship->setRump($trumfieldRump);
        $ship->setHuell((int) ceil($ship->getMaxHull() / 20));
        $ship->setUser($this->userRepository->getFallbackUser());
        $ship->setBuildplan(null);
        $ship->setSpacecraftType(SpacecraftTypeEnum::SPACECRAFT_TYPE_OTHER);
        $ship->setShield(0);
        $ship->setAlertStateGreen();
        $ship->setInfluenceArea(null);
        $ship->setName(_('Trümmer'));
        $ship->setIsDestroyed(true);
        $this->shipStateChanger->changeShipState($destroyedShipWrapper, ShipStateEnum::SHIP_STATE_DESTROYED);

        // delete ship systems
        $this->shipSystemRepository->truncateByShip($ship->getId());
        $ship->getSystems()->clear();

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($destroyedShipWrapper);
    }
}
