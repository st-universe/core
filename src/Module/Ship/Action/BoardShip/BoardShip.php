<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BoardShip;

use request;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\CloseCombat\CloseCombatUtilInterface;
use Stu\Module\Ship\Lib\Interaction\ThreatReactionInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollection;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class BoardShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BOARD_SHIP';

    private CrewRepositoryInterface $crewRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    private NbsUtilityInterface $nbsUtility;

    private CloseCombatUtilInterface $closeCombatUtil;

    private ThreatReactionInterface $threatReaction;

    private FightLibInterface $fightLib;

    private ShipStateChangerInterface $shipStateChanger;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private DistributedMessageSenderInterface $distributedMessageSender;

    private StuRandom $stuRandom;

    public function __construct(
        CrewRepositoryInterface $crewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker,
        NbsUtilityInterface $nbsUtility,
        CloseCombatUtilInterface $closeCombatUtil,
        ThreatReactionInterface $threatReaction,
        FightLibInterface $fightLib,
        ShipStateChangerInterface $shipStateChanger,
        ShipSystemManagerInterface $shipSystemManager,
        ShipTakeoverManagerInterface $shipTakeoverManager,
        CreatePrestigeLogInterface $createPrestigeLog,
        DistributedMessageSenderInterface $distributedMessageSender,
        StuRandom $stuRandom
    ) {
        $this->crewRepository = $crewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
        $this->nbsUtility = $nbsUtility;
        $this->closeCombatUtil = $closeCombatUtil;
        $this->threatReaction = $threatReaction;
        $this->fightLib = $fightLib;
        $this->shipStateChanger = $shipStateChanger;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipTakeoverManager = $shipTakeoverManager;
        $this->createPrestigeLog = $createPrestigeLog;
        $this->distributedMessageSender = $distributedMessageSender;
        $this->stuRandom = $stuRandom;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

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

        if (!$target->isBoardingPossible()) {
            return;
        }

        if ($target->getUser() === $user) {
            return;
        }

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

        if (!$this->fightLib->canAttackTarget($ship, $target, false)) {
            throw new SanityCheckException('Target cant be attacked', self::ACTION_IDENTIFIER);
        }

        if ($target->getCloakState() && !$this->nbsUtility->isTachyonActive($ship)) {
            throw new SanityCheckException('Attacked cloaked ship without active tachyon', self::ACTION_IDENTIFIER);
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

        if ($this->fightLib->isTargetOutsideFinishedTholianWeb($ship, $target)) {
            $game->addInformation(_('Das Ziel ist nicht mit im Energienetz gefangen'));
            return;
        }

        $neededPrestige = $this->shipTakeoverManager->getPrestigeForBoardingAttempt($target);
        if ($user->getPrestige() < $neededPrestige) {
            $game->addInformation(sprintf(
                'Nicht genügend Prestige vorhanden, benötigt wird: %d',
                $neededPrestige
            ));
            return;
        }

        $shipName = $ship->getName();
        $targetName = $target->getName();

        $this->threatReaction->reactToThreat(
            $wrapper,
            $targetWrapper,
            sprintf(
                "Die %s versucht die %s in Sektor %s zu entern.",
                $shipName,
                $targetName,
                $ship->getSectorString()
            )
        );

        if ($ship->isDestroyed()) {
            return;
        }

        $combatGroupAttacker = $this->closeCombatUtil->getCombatGroup($ship);
        $combatGroupDefender = $this->closeCombatUtil->getCombatGroup($target);

        $messages = new MessageCollection();
        $messages->add(new Message(null, null, [sprintf(
            'Die %s entsendet ein Enterkommando auf die %s',
            $ship->getName(),
            $target->getName()
        )]));

        while (!empty($combatGroupAttacker) && !empty($combatGroupDefender)) {
            $this->shipStateChanger->changeShipState($targetWrapper, ShipStateEnum::SHIP_STATE_NONE);
            $this->createPrestigeLog->createLog(
                -$neededPrestige,
                sprintf(
                    '-%d Prestige erhalten für einen Enterversuch auf die %s von Spieler %s',
                    $neededPrestige,
                    $target->getName(),
                    $target->getUser()->getName()
                ),
                $user,
                time()
            );

            $this->cycleKillRound(
                $combatGroupAttacker,
                $combatGroupDefender,
                $wrapper,
                $targetWrapper,
                $messages
            );
        }

        if (empty($combatGroupAttacker)) {
            $messages->add(new Message(null, null, ['Der Enterversuch ist gescheitert']));
        } else if (empty($combatGroupDefender)) {

            $messages->add(new Message(null, null, [sprintf(
                'Die %s wurde geentert. Übernahme kann nun erfolgen.',
                $target->getName()
            )]));
        }

        $this->sendPms(
            $userId,
            $ship->getSectorString(),
            $messages,
            $target->isBase()
        );

        $informations = $messages->getInformationDump();

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $game->addInformationWrapper($informations);
    }

    /**
     * @param array<ShipCrewInterface> $attackers
     * @param array<ShipCrewInterface> $defenders
     */
    private function cycleKillRound(
        array &$attackers,
        array &$defenders,
        ShipWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();
        $target = $targetWrapper->get();

        $combatValueAttacker = $this->closeCombatUtil->getCombatValue($attackers, $ship->getUser()->getFaction());
        $combatValueDefender = $this->closeCombatUtil->getCombatValue($defenders, $target->getUser()->getFaction());

        $rand = $this->stuRandom->rand(
            0,
            $combatValueAttacker + $combatValueDefender
        );

        $isDeathOnDefenderSide = $rand <= $combatValueAttacker;
        if ($isDeathOnDefenderSide) {
            $killedShipCrew = $this->getKilledCrew($defenders, $targetWrapper);
        } else {
            $killedShipCrew = $this->getKilledCrew($attackers, $wrapper);
        }

        $message = new Message();
        $message->add(sprintf(
            '%s %s von der %s wurde im Kampf getötet',
            $killedShipCrew->getCrew()->getTypeDescription(),
            $killedShipCrew->getCrew()->getName(),
            $isDeathOnDefenderSide ? $target->getName() : $ship->getName()
        ));
        $messages->add($message);
    }

    /**
     * @param array<int, ShipCrewInterface> &$combatGroup
     */
    private function getKilledCrew(array &$combatGroup, ShipWrapperInterface $wrapper): ShipCrewInterface
    {
        $keys = array_keys($combatGroup);
        shuffle($keys);

        $randomKey = current($keys);

        $killedShipCrew = $combatGroup[$randomKey];
        unset($combatGroup[$randomKey]);

        $ship = $wrapper->get();
        $ship->getCrewAssignments()->removeElement($killedShipCrew);
        $this->crewRepository->delete($killedShipCrew->getCrew());
        $this->shipCrewRepository->delete($killedShipCrew);

        if ($ship->getCrewAssignments()->isEmpty()) {
            $this->shipSystemManager->deactivateAll($wrapper);
        }

        return $killedShipCrew;
    }

    private function sendPms(
        int $userId,
        string $sectorString,
        MessageCollectionInterface $messageCollection,
        bool $isTargetBase
    ): void {

        $header = sprintf(
            _("Enterversuch in Sektor %s"),
            $sectorString
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollection,
            $userId,
            $isTargetBase ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $header
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
