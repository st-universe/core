<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EscapeTractorBeam;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class EscapeTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ESCAPE_TRAKTOR';

    private ShipLoaderInterface $shipLoader;

    private ApplyDamageInterface $applyDamage;

    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRemoverInterface $shipRemover;

    private EntryCreatorInterface $entryCreator;

    private AlertRedHelperInterface $alertRedHelper;

    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ApplyDamageInterface $applyDamage,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        EntryCreatorInterface $entryCreator,
        AlertRedHelperInterface $alertRedHelper,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->applyDamage = $applyDamage;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->entryCreator = $entryCreator;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipSystemManager = $shipSystemManager;
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
        $chance = rand(1, 100);
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

        $tractoringShip->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setStatus(0);
        $this->shipSystemManager->deactivate($tractoringShipWrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); // forced active deactivation

        $this->shipRepository->save($tractoringShip);

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $tractoringShip->getId());

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $tractoringShip->getUser()->getId(),
            sprintf(_('Bei dem Fluchtversuch der %s wurde der Traktorstrahl der %s in Sektor %s zerstört'), $ship->getName(), $tractoringShip->getName(), $ship->getSectorString()),
            $tractoringShip->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );

        $game->addInformation(_('Der Fluchtversuch ist gelungen'));

        //Alarm-Rot check
        $this->alertRedHelper->doItAll($ship, $game);
    }

    private function sufferDeflectorDamage(
        ShipInterface $tractoringShip,
        ShipWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {
        $msg = [];
        $msg[] = _('Der Fluchtversuch ist fehlgeschlagen:');

        $ship = $wrapper->get();
        $system = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_DEFLECTOR);
        $this->applyDamage->damageShipSystem($wrapper, $system, rand(5, 25), $msg);

        $game->addInformationMergeDown($msg);

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $tractoringShip->getId());

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

        $damageMsg = $this->applyDamage->damage(new DamageWrapper((int) ceil($ship->getMaxHull() * rand(10, 25) / 100)), $wrapper);
        $game->addInformationMergeDown($damageMsg);

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $tractoringShip->getId());

        if ($ship->isDestroyed()) {
            $this->entryCreator->addShipEntry(
                'Die ' . $shipName . ' (' . $ship->getRump()->getName() . ') wurde bei einem Fluchtversuch in Sektor ' . $ship->getSectorString() . ' zerstört',
                $ship->getUser()->getId()
            );

            $destroyMsg = $this->shipRemover->destroy($wrapper);
            if ($destroyMsg !== null) {
                $game->addInformation($destroyMsg);
            }

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
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
