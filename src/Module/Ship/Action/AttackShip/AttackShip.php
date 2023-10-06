<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackShip;

use request;
use RuntimeException;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

//TODO unit tests and request class
final class AttackShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_SHIP';

    private ShipLoaderInterface $shipLoader;

    private DistributedMessageSenderInterface $distributedMessageSender;

    private ShipAttackCycleInterface $shipAttackCycle;

    private InteractionCheckerInterface $interactionChecker;

    private AlertRedHelperInterface $alertRedHelper;

    private NbsUtilityInterface $nbsUtility;

    private FightLibInterface $fightLib;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        DistributedMessageSenderInterface $distributedMessageSender,
        ShipAttackCycleInterface $shipAttackCycle,
        InteractionCheckerInterface $interactionChecker,
        AlertRedHelperInterface $alertRedHelper,
        NbsUtilityInterface $nbsUtility,
        FightLibInterface $fightLib,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->distributedMessageSender = $distributedMessageSender;
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
        if ($ship->getDockedTo() !== null) {
            $ship->setDockedTo(null);
        }

        $isTargetBase = $target->isBase();

        [$attacker, $defender, $isFleetFight, $isWebSituation] = $this->getAttackerDefender($wrapper, $targetWrapper);

        $messageCollection = $this->shipAttackCycle->cycle($attacker, $defender, $isWebSituation);

        $this->sendPms(
            $userId,
            $ship->getSectorString(),
            $messageCollection,
            !$isWebSituation && $isTargetBase
        );

        $informations = $messageCollection->getInformationDump();

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

        if ($ship->isDestroyed()) {
            $game->addInformationWrapper($informations);
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if ($isFleetFight) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $informations->getInformations());
        } else {
            $game->addInformationWrapper($informations);
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
    private function getAttackerDefender(ShipWrapperInterface $wrapper, ShipWrapperInterface $targetWrapper): array
    {
        $ship = $wrapper->get();

        [$attacker, $defender, $isFleetFight] = $this->fightLib->getAttackerDefender($wrapper, $targetWrapper);

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

    public function performSessionCheck(): bool
    {
        return true;
    }
}
