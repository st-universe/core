<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EscapeTractorBeam;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class EscapeTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ESCAPE_TRAKTOR';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ApplyDamageInterface $applyDamage,
        private ShipRepositoryInterface $shipRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private ShipDestructionInterface $shipDestruction,
        private AlertRedHelperInterface $alertRedHelper,
        private ShipSystemManagerInterface $shipSystemManager
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        // is ship trapped in tractor beam?
        if (!$ship->isTractored()) {
            return;
        }

        //is deflector working?
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR)) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        $tractoringShipWrapper = $wrapper->getTractoringShipWrapper();
        if ($tractoringShipWrapper === null) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        //enough energy?
        if ($epsSystem === null || $epsSystem->getEps() < 20) {
            $game->addInformation(sprintf(_('Nicht genug Energie für Fluchtversuch (%d benötigt)'), 20));
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        //eps cost
        $epsSystem->lowerEps(20)->update();

        $tractoringShip = $tractoringShipWrapper->get();

        //parameters
        $ownMass = $ship->getRump()->getTractorMass();
        $otherPayload = $tractoringShip->getTractorPayload();
        $ratio = $ownMass / $otherPayload;

        // probabilities
        $chance = random_int(1, 100);
        if ($chance < (int)ceil(11 * $ratio)) {
            $this->escape($tractoringShipWrapper, $wrapper, $game);
        } elseif ($chance < 55) {
            $this->sufferDeflectorDamage($tractoringShip, $wrapper, $game);
        } else {
            $this->sufferHullDamage($tractoringShip, $wrapper, $game);
        }

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->shipRepository->save($ship);
    }

    private function escape(
        ShipWrapperInterface $tractoringShipWrapper,
        ShipWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $ship = $wrapper->get();
        $tractoringShip = $tractoringShipWrapper->get();
        $isTractoringShipWarped = $tractoringShip->getWarpDriveState();

        $tractoringShip->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setStatus(0);
        $this->shipSystemManager->deactivate($tractoringShipWrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); // forced active deactivation

        $this->shipRepository->save($tractoringShip);

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $tractoringShip->getId());

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $tractoringShip->getUser()->getId(),
            sprintf(_('Bei dem Fluchtversuch der %s wurde der Traktorstrahl der %s in Sektor %s zerstört'), $ship->getName(), $tractoringShip->getName(), $ship->getSectorString()),
            $tractoringShip->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );

        $game->addInformation(_('Der Fluchtversuch ist gelungen'));

        //Alarm-Rot check
        if ($isTractoringShipWarped) {
            $this->alertRedHelper->doItAll($ship, $game);
        }
    }

    private function sufferDeflectorDamage(
        ShipInterface $tractoringShip,
        ShipWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $informations = new InformationWrapper([_('Der Fluchtversuch ist fehlgeschlagen:')]);

        $ship = $wrapper->get();
        $system = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_DEFLECTOR);
        $this->applyDamage->damageShipSystem($wrapper, $system, random_int(5, 25), $informations);

        $game->addInformationWrapper($informations);

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $tractoringShip->getId());

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $tractoringShip->getUser()->getId(),
            sprintf(_('Der Fluchtversuch der %s ist gescheitert'), $ship->getName()),
            $tractoringShip->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );
    }

    private function sufferHullDamage(
        ShipInterface $tractoringShip,
        ShipWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $ship = $wrapper->get();
        $otherUserId = $tractoringShip->getUser()->getId();
        $shipName = $ship->getName();

        $game->addInformation(_('Der Fluchtversuch ist fehlgeschlagen:'));

        $informations = $this->applyDamage->damage(new DamageWrapper((int) ceil($ship->getMaxHull() * random_int(10, 25) / 100)), $wrapper);

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $tractoringShip->getId());

        if ($ship->isDestroyed()) {

            $this->shipDestruction->destroy(
                $tractoringShip,
                $wrapper,
                ShipDestructionCauseEnum::ESCAPE_TRACTOR,
                $informations
            );

            $this->privateMessageSender->send(
                $ship->getUser()->getId(),
                $otherUserId,
                sprintf(_('Die %s wurde beim Fluchtversuch zerstört'), $shipName),
                $tractoringShip->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        } else {
            $this->privateMessageSender->send(
                $ship->getUser()->getId(),
                $otherUserId,
                sprintf(_('Der Fluchtversuch der %s ist gescheitert'), $shipName),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }

        $game->addInformationWrapper($informations);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
