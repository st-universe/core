<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use RuntimeException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShipAttackCore implements ShipAttackCoreInterface
{
    private DistributedMessageSenderInterface $distributedMessageSender;

    private ShipAttackCycleInterface $shipAttackCycle;

    private AlertRedHelperInterface $alertRedHelper;

    private FightLibInterface $fightLib;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        DistributedMessageSenderInterface $distributedMessageSender,
        ShipAttackCycleInterface $shipAttackCycle,
        AlertRedHelperInterface $alertRedHelper,
        FightLibInterface $fightLib,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->distributedMessageSender = $distributedMessageSender;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->alertRedHelper = $alertRedHelper;
        $this->fightLib = $fightLib;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function foo(
        ShipWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        bool &$isFleetFight,
        InformationWrapper $informations
    ): void {
        $ship = $wrapper->get();
        $target = $targetWrapper->get();
        $userId = $ship->getUser()->getId();
        $isTargetBase = $target->isBase();

        [$attacker, $defender, $isFleetFight, $isWebSituation] = $this->getAttackersAndDefenders($wrapper, $targetWrapper);

        $messageCollection = $this->shipAttackCycle->cycle($attacker, $defender, $isWebSituation);

        $this->sendPms(
            $userId,
            $ship->getSectorString(),
            $messageCollection,
            !$isWebSituation && $isTargetBase
        );

        $informations->addInformationWrapper($messageCollection->getInformationDump());

        if ($this->isActiveTractorShipWarped($ship, $target)) {
            //Alarm-Rot check for ship
            if (!$ship->isDestroyed()) {
                $informations->addInformationWrapper($this->alertRedHelper->doItAll($ship));
            }

            //Alarm-Rot check for traktor ship
            if (!$this->isTargetDestroyed($target)) {
                $informations->addInformationWrapper($this->alertRedHelper->doItAll($target));
            }
        }
    }

    private function isTargetDestroyed(ShipInterface $ship): bool
    {
        return $ship->isDestroyed();
    }

    private function isActiveTractorShipWarped(ShipInterface $ship, ShipInterface $target): bool
    {
        $tractoringShip = $ship->getTractoringShip();
        if ($tractoringShip === null) {
            return false;
        }

        if ($tractoringShip !== $target) {
            return false;
        } else {
            return $target->getWarpState();
        }
    }

    private function sendPms(
        int $userId,
        string $sectorString,
        MessageCollectionInterface $messageCollection,
        bool $isTargetBase
    ): void {

        $header = sprintf(
            _("Kampf in Sektor %s"),
            $sectorString
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollection,
            $userId,
            $isTargetBase ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $header
        );
    }

    /**
     * @return array{0: array<int, ShipWrapperInterface>, 1: array<int, ShipWrapperInterface>, 2: bool, 3: bool}
     */
    private function getAttackersAndDefenders(ShipWrapperInterface $wrapper, ShipWrapperInterface $targetWrapper): array
    {
        $ship = $wrapper->get();

        [$attacker, $defender, $isFleetFight] = $this->fightLib->getAttackersAndDefenders($wrapper, $targetWrapper);

        $isWebSituation = $this->fightLib->isTargetOutsideFinishedTholianWeb($ship, $targetWrapper->get());

        //if in tholian web and defenders outside, reflect damage
        if ($isWebSituation) {
            $holdingWeb = $ship->getHoldingWeb();
            if ($holdingWeb === null) {
                throw new RuntimeException('this should not happen');
            }

            $defender = $this->shipWrapperFactory->wrapShips($holdingWeb->getCapturedShips()->toArray());
        }

        return [
            $attacker,
            $defender,
            $isFleetFight,
            $isWebSituation
        ];
    }
}
