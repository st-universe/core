<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateTractorBeam;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\TractorBeamShipSystem;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Interaction\ThreatReactionInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ActivateTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_TRACTOR';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private InteractionCheckerInterface $interactionChecker;

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipStateChangerInterface $shipStateChanger;

    private ThreatReactionInterface $threatReaction;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        InteractionCheckerInterface $interactionChecker,
        ActivatorDeactivatorHelperInterface $helper,
        ShipSystemManagerInterface $shipSystemManager,
        ShipStateChangerInterface $shipStateChanger,
        ThreatReactionInterface $threatReaction
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->interactionChecker = $interactionChecker;
        $this->helper = $helper;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipStateChanger = $shipStateChanger;
        $this->threatReaction = $threatReaction;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::indInt('id');
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

        $shipName = $ship->getName();

        if (!$this->interactionChecker->checkPosition($ship, $target)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }
        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        if (!TractorBeamShipSystem::isTractorBeamPossible($target)) {
            return;
        }

        $targetName = $target->getName();

        // activate system
        if (!$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, $game)) {
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        if ($target->getSpacecraftType() !== SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP) {
            $game->addInformation("Das Ziel kann nicht erfasst werden");
            $this->abort($wrapper, $game);
            return;
        }

        $tractoringShip = $target->getTractoringShip();
        if ($tractoringShip !== null) {
            $game->addInformation("Das Schiff wird bereits vom Traktorstrahl der " . $tractoringShip->getName() . " gehalten");
            $this->abort($wrapper, $game);
            return;
        }
        if ($target->getHoldingWeb() !== null && $target->getHoldingWeb()->isFinished()) {
            $game->addInformation("Ziel kann nicht erfasst werden, da es in einem Energienetz gefangen ist");
            $this->abort($wrapper, $game);
            return;
        }
        if ($target->getFleetId() && $target->getFleetId() == $ship->getFleetId()) {
            $game->addInformation("Die " . $targetName . " befindet sich in der selben Flotte wie die " . $shipName);
            $this->abort($wrapper, $game);
            return;
        }

        $this->threatReaction->reactToThreat(
            $wrapper,
            $targetWrapper,
            sprintf(
                "Die %s versucht die %s in Sektor %s mit dem Traktorstrahl zu erfassen.",
                $shipName,
                $targetName,
                $ship->getSectorString()
            )
        );

        if ($ship->isDestroyed()) {
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        //is tractor beam system still healthy?
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)) {
            $game->addInformation("Der Traktorstrahl wurde bei dem Angriff zerstört");
            return;
        }
        if ($target->isDestroyed()) {
            $game->addInformation("Das Ziel wurde bei dem Angriff zerstört");
            $this->abort($wrapper, $game);
            return;
        }

        //is nbs system still healthy?
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_NBS)) {
            $game->addInformation("Abbruch, die Nahbereichssensoren wurden bei dem Angriff zerstört");
            $this->abort($wrapper, $game);
            return;
        }


        if ($target->getShieldState()) {
            $game->addInformation("Die " . $targetName . " kann aufgrund der aktiven Schilde nicht erfasst werden");
            $this->abort($wrapper, $game);
            return;
        }
        if ($target->isTractoring()) {
            $this->shipSystemManager->deactivate($targetWrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //forced active deactivation
        }

        $this->shipStateChanger->changeShipState($targetWrapper, ShipStateEnum::SHIP_STATE_NONE);

        $target->setDockedTo(null);
        $ship->setTractoredShip($target);
        $this->shipRepository->save($ship);
        $this->shipRepository->save($target);

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            "Die " . $targetName . " wurde in Sektor " . $ship->getSectorString() . " vom Traktorstrahl der " . $shipName . " erfasst",
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId())
        );
        $game->addInformation("Der Traktorstrahl wurde auf die " . $targetName . " gerichtet");
    }

    private function abort(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        //deactivate system
        if (!$this->helper->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, $game)) {
            throw new SystemNotDeactivatableException('TRACTOR ERROR');
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
