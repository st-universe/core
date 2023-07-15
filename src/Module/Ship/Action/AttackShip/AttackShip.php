<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackShip;

use request;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Exception\SanityCheckException;
use Stu\Lib\InformationWrapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;

//TODO unit tests and request class
final class AttackShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_SHIP';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipAttackCycleInterface $shipAttackCycle;

    private InteractionCheckerInterface $interactionChecker;

    private AlertRedHelperInterface $alertRedHelper;

    private NbsUtilityInterface $nbsUtility;

    private FightLibInterface $fightLib;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipAttackCycleInterface $shipAttackCycle,
        InteractionCheckerInterface $interactionChecker,
        AlertRedHelperInterface $alertRedHelper,
        NbsUtilityInterface $nbsUtility,
        FightLibInterface $fightLib,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->interactionChecker = $interactionChecker;
        $this->alertRedHelper = $alertRedHelper;
        $this->nbsUtility = $nbsUtility;
        $this->fightLib = $fightLib;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($target, $ship)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }

        // no attack on self or own fleet
        if ($this->isAttackOnSelfOrOwnFleet($ship, $target)) {
            return;
        }

        if ($this->isTargetDestroyed($target)) {
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            $game->addInformation(_('Das Ziel ist bereits zerstört'));
            return;
        }

        if (!$this->fightLib->canAttackTarget($ship, $target)) {
            throw new SanityCheckException('Target cant be attacked', self::ACTION_IDENTIFIER);
        }

        if ($target->getCloakState() && !$this->nbsUtility->isTachyonActive($ship)) {
            throw new SanityCheckException('Attacked cloaked ship without active tachyon', self::ACTION_IDENTIFIER);
        }

        if ($target->getRump()->isTrumfield()) {
            return;
        }

        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null || $epsSystemData->getEps() === 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }

        if ($ship->isDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }
        if ($ship->getDockedTo()) {
            $ship->setDockedTo(null);
        }

        $isTargetBase = $target->isBase();

        [$attacker, $defender, $fleet, $isWebSituation] = $this->getAttackerDefender($ship, $target);

        $fightMessageCollection = $this->shipAttackCycle->cycle(
            $this->shipWrapperFactory->wrapShips($attacker),
            $this->shipWrapperFactory->wrapShips($defender),
            $isWebSituation
        );

        $this->sendPms(
            $userId,
            $ship->getSectorString(),
            $fightMessageCollection,
            !$isWebSituation && $isTargetBase
        );

        $informations = new InformationWrapper($fightMessageCollection->getMessageDump());

        if ($this->isActiveTractorShipWarped($ship, $target)) {
            //Alarm-Rot check for ship
            if (!$ship->isDestroyed()) {
                $alertRedInformations = $this->alertRedHelper->doItAll($ship, null);

                if ($alertRedInformations !== null) {
                    $informations->addInformationMerge($alertRedInformations->getInformations());
                }
            }

            //Alarm-Rot check for traktor ship
            if (!$this->isTargetDestroyed($target)) {
                $alertRedInformations = $this->alertRedHelper->doItAll($target, null);

                if ($alertRedInformations !== null) {
                    $informations->addInformationMerge($alertRedInformations->getInformations());
                }
            }
        }

        if ($ship->isDestroyed()) {
            $game->addInformationMerge($informations->getInformations());
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if ($fleet) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $informations->getInformations());
        } else {
            $game->addInformationMerge($informations->getInformations());
            $game->setTemplateVar('FIGHT_RESULTS', null);
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

    private function isAttackOnSelfOrOwnFleet(ShipInterface $ship, ShipInterface $target): bool
    {
        if ($target === $ship) {
            return true;
        }

        $ownFleet = $ship->getFleet();
        $targetFleet = $target->getFleet();

        if ($ownFleet === null || $targetFleet === null) {
            return false;
        }

        return $targetFleet === $ownFleet;
    }

    private function sendPms(
        int $userId,
        string $sectorString,
        FightMessageCollectionInterface $messageCollection,
        bool $isTargetBase
    ): void {
        foreach ($messageCollection->getRecipientIds() as $recipientId) {
            $messageDump = $messageCollection->getMessageDump($recipientId);

            $pm = sprintf(_('Kampf in Sektor %s') . "\n", $sectorString);
            foreach ($messageDump as $value) {
                $pm .= $value . "\n";
            }

            $this->privateMessageSender->send(
                $userId,
                $recipientId,
                $pm,
                $isTargetBase ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
    }

    /**
     * @return array{0: array<int, ShipInterface>, 1: array<int, ShipInterface>, 2: bool, 3: bool}
     */
    private function getAttackerDefender(ShipInterface $ship, ShipInterface $target): array
    {
        $fleet = false;

        if ($ship->isFleetLeader() && $ship->getFleet() !== null) {
            $attacker = $ship->getFleet()->getShips()->toArray();
            $fleet = true;
        } else {
            $attacker = [$ship->getId() => $ship];
        }
        if ($target->getFleet() !== null) {
            $defender = [];

            // only uncloaked defenders fight
            /**
             * @var ShipInterface $defShip
             */
            foreach ($target->getFleet()->getShips()->toArray() as $defShip) {
                if (!$defShip->getCloakState()) {
                    $defender[$defShip->getId()] = $defShip;

                    if (
                        $defShip->getDockedTo() !== null
                        && !$defShip->getDockedTo()->getUser()->isNpc()
                        && $defShip->getDockedTo()->hasActiveWeapon()
                    ) {
                        $defender[$defShip->getDockedTo()->getId()] = $defShip->getDockedTo();
                    }
                }
            }

            // if all defenders were cloaked, they obviously were scanned and enter the fight as a whole fleet
            if (empty($defender)) {
                $defender = $target->getFleet()->getShips()->toArray();
            }

            $fleet = true;
        } else {
            $defender = [$target->getId() => $target];

            if (
                $target->getDockedTo() !== null
                && !$target->getDockedTo()->getUser()->isNpc()
                && $target->getDockedTo()->hasActiveWeapon()
            ) {
                $defender[$target->getDockedTo()->getId()] = $target->getDockedTo();
            }
        }

        $isWebSituation = false;

        //if in tholian web and defenders outside, reflect damage
        if ($this->isTargetingOutsideTholianWeb($ship, $target)) {
            $isWebSituation = true;
            $defender = [];

            /** @var TholianWebInterface */
            $holdingWeb = $ship->getHoldingWeb();

            foreach ($holdingWeb->getCapturedShips() as $shipInWeb) {
                $defender[$shipInWeb->getId()] = $shipInWeb;
            }
        }

        return [
            $attacker,
            $defender,
            $fleet,
            $isWebSituation
        ];
    }

    private function isTargetingOutsideTholianWeb(ShipInterface $ship, ShipInterface $target): bool
    {
        return $ship->getHoldingWeb() !== null
            && $ship->getHoldingWeb()->isFinished()
            && ($target->getHoldingWeb() !== $ship->getHoldingWeb());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
