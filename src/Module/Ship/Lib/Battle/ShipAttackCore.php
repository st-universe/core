<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use RuntimeException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShipAttackCore implements ShipAttackCoreInterface
{
    public function __construct(
        private DistributedMessageSenderInterface $distributedMessageSender,
        private ShipAttackCycleInterface $shipAttackCycle,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private FightLibInterface $fightLib,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private BattlePartyFactoryInterface $battlePartyFactory
    ) {
    }

    public function attack(
        ShipWrapperInterface|FleetWrapperInterface $sourceWrapper,
        ShipWrapperInterface $targetWrapper,
        bool &$isFleetFight,
        InformationWrapper $informations
    ): void {
        $wrapper = $sourceWrapper instanceof ShipWrapperInterface ?  $sourceWrapper : $sourceWrapper->getLeadWrapper();
        $ship = $wrapper->get();

        $target = $targetWrapper->get();
        $userId = $ship->getUser()->getId();
        $isTargetBase = $target->isBase();

        $isActiveTractorShipWarped = $this->isActiveTractorShipWarped($ship, $target);

        [$attacker, $defender, $isFleetFight, $attackCause] = $this->getAttackersAndDefenders(
            $wrapper,
            $targetWrapper
        );

        $messageCollection = $this->shipAttackCycle->cycle($attacker, $defender, $attackCause);

        $this->sendPms(
            $userId,
            $ship->getSectorString(),
            $messageCollection,
            ($attackCause !== ShipAttackCauseEnum::THOLIAN_WEB_REFLECTION) && $isTargetBase
        );

        $informations->addInformationWrapper($messageCollection->getInformationDump());

        if ($isActiveTractorShipWarped) {
            //Alarm-Rot check for ship
            if (!$ship->isDestroyed()) {
                $this->alertReactionFacade->doItAll($wrapper, $informations);
            }

            //Alarm-Rot check for traktor ship
            if (!$this->isTargetDestroyed($target)) {
                $this->alertReactionFacade->doItAll($targetWrapper, $informations);
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
            return $target->getWarpDriveState();
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
     * @return array{0: AttackingBattleParty, 1: BattlePartyInterface, 2: bool, 3: ShipAttackCauseEnum}
     */
    private function getAttackersAndDefenders(ShipWrapperInterface|FleetWrapperInterface $wrapper, ShipWrapperInterface $targetWrapper): array
    {
        $ship = $wrapper instanceof ShipWrapperInterface ?  $wrapper->get() : $wrapper->get()->getLeadShip();

        [$attacker, $defender, $isFleetFight] = $this->fightLib->getAttackersAndDefenders($wrapper, $targetWrapper, $this->battlePartyFactory);

        $isWebSituation = $this->fightLib->isTargetOutsideFinishedTholianWeb($ship, $targetWrapper->get());

        //if in tholian web and defenders outside, reflect damage
        if ($isWebSituation) {
            $holdingWeb = $ship->getHoldingWeb();
            if ($holdingWeb === null) {
                throw new RuntimeException('this should not happen');
            }

            $defender = $this->battlePartyFactory->createMixedBattleParty(
                $this->shipWrapperFactory->wrapShips($holdingWeb->getCapturedShips()->toArray())
            );
        }

        return [
            $attacker,
            $defender,
            $isFleetFight,
            $isWebSituation ? ShipAttackCauseEnum::THOLIAN_WEB_REFLECTION : ShipAttackCauseEnum::SHIP_FIGHT
        ];
    }
}
