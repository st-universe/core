<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\BoardShip;

use Override;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\BoardShipUtilInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\CloseCombatUtilInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipInteractionEnum;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ThreatReactionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollection;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class BoardShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BOARD_SHIP';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private CloseCombatUtilInterface $closeCombatUtil,
        private ThreatReactionInterface $threatReaction,
        private FightLibInterface $fightLib,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ShipTakeoverManagerInterface $shipTakeoverManager,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory,
        private BoardShipUtilInterface $boardShip,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private DistributedMessageSenderInterface $distributedMessageSender,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $spacecraft = $wrapper->get();

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

        if ($target->getCrewCount() === 0) {
            return;
        }

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($spacecraft)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_ENABLED,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_SOURCE_TACHYON,
                InteractionCheckType::EXPECT_TARGET_NO_VACATION,
                InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB
            ])
            ->check($game)) {
            return;
        }

        if (!$this->fightLib->canAttackTarget($spacecraft, $target, false, false)) {
            throw new SanityCheckException('Target cant be attacked', self::ACTION_IDENTIFIER);
        }

        if ($spacecraft->getCrewCount() === 0) {
            $game->addInformation(_('Aktion nicht möglich, keine Crew vorhanden!'));
            return;
        }

        $lastTakeover = $user->getLastBoarding();
        if (
            $lastTakeover !== null
            && time() < $lastTakeover +  ShipTakeoverManagerInterface::BOARDING_COOLDOWN_IN_SECONDS
        ) {
            $game->addInformation(sprintf(
                'Enterkommando kann erst wieder um %s entsendet werden',
                date('H:i', $lastTakeover +  ShipTakeoverManagerInterface::BOARDING_COOLDOWN_IN_SECONDS)
            ));
            return;
        }

        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null || $epsSystemData->getEps() === 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
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

        $targetUserId = $target->getUser()->getId();

        $this->threatReaction->reactToThreat(
            $wrapper,
            $targetWrapper,
            ShipInteractionEnum::BOARD_SHIP
        );

        if ($spacecraft->isDestroyed()) {
            $game->setView(ModuleEnum::SHIP);
            return;
        }

        $combatGroupAttacker = $this->closeCombatUtil->getCombatGroup($spacecraft);
        $combatGroupDefender = $this->closeCombatUtil->getCombatGroup($target);

        $messages = $this->messageFactory->createMessageCollection();
        $message = $this->messageFactory->createMessage($userId, $targetUserId, [sprintf(
            'Die %s entsendet ein Enterkommando auf die %s',
            $spacecraft->getName(),
            $target->getName()
        )]);

        $messages->add($message);

        $this->spacecraftStateChanger->changeState($targetWrapper, SpacecraftStateEnum::NONE);

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

        while (!empty($combatGroupAttacker) && !empty($combatGroupDefender)) {
            $this->boardShip->cycleKillRound(
                $combatGroupAttacker,
                $combatGroupDefender,
                $wrapper,
                $targetWrapper,
                $messages
            );
        }

        $message = $this->messageFactory->createMessage($userId, $targetUserId);
        $messages->add($message);

        if (empty($combatGroupAttacker)) {
            $message->add('Der Enterversuch ist gescheitert');
        } elseif ($target->getCrewAssignments()->isEmpty()) {
            $message->add(sprintf(
                'Die Crew der %s wurde getötet. Übernahme kann nun erfolgen.',
                $target->getName()
            ));
        } else {
            $message->add(sprintf(
                'Es leben noch %d Crewman auf der %s.',
                $target->getCrewCount(),
                $target->getName()
            ));
        }

        $user->setLastBoarding(time());
        $this->userRepository->save($user);

        $this->sendPms(
            $userId,
            $spacecraft->getSectorString(),
            $messages,
            $target->isStation()
        );

        $informations = $messages->getInformationDump();

        $game->addInformationWrapper($informations);
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
            $isTargetBase ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $header
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
