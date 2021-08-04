<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EscapeTractorBeam;

use request;

use Stu\Lib\DamageWrapper;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
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

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ApplyDamageInterface $applyDamage,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        EntryCreatorInterface $entryCreator
    ) {
        $this->shipLoader = $shipLoader;
        $this->applyDamage = $applyDamage;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->entryCreator = $entryCreator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        // is ship trapped in tractor beam?
        if ($ship->getTraktormode() != 2) {
            return;
        }

        //is deflector working?
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR)) {
            return;
        }

        //enough energy?
        if ($ship->getEps() < 20) {
            $game->addInformation(sprintf(_('Nicht genug Energie für Fluchtversuch (%d benötigt)'), 20));
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        //eps cost
        $ship->setEps($ship->getEps() - 20);

        // probabilities
        $chance = rand(1, 100);
        if ($chance < 11) {
            $this->escape($ship, $game);
        } elseif ($chance < 55) {
            $this->sufferDeflectorDamage($ship, $game);
        } else {

            $this->sufferHullDamage($ship, $game);
        }

        if ($ship->getIsDestroyed()) {

            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->shipRepository->save($ship);
    }

    private function escape(ShipInterface $ship, $game): void
    {
        $otherShip = $ship->getTraktorShip();

        $otherShip->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setStatus(0);
        $otherShip->deactivateTraktorBeam();

        $this->shipRepository->save($otherShip);

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $otherShip->getId());

        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $otherShip->getUser()->getId(),
            sprintf(_('Bei dem Fluchtversuch der %s wurde der Traktorstrahl der %s in Sektor %s zerstört'), $ship->getName(), $otherShip->getName(), $ship->getSectorString()),
            $otherShip->isBase() ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );

        $game->addInformation(_('Der Fluchtversuch ist gelungen'));
    }

    private function sufferDeflectorDamage(ShipInterface $ship, GameControllerInterface $game): void
    {
        $msg = [];
        $msg[] = _('Der Fluchtversuch ist fehlgeschlagen:');

        $system = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_DEFLECTOR);
        $this->applyDamage->damageShipSystem($ship, $system, rand(5, 25), $msg);

        $game->addInformationMergeDown($msg);

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getTraktorShip()->getId());

        $this->privateMessageSender->send(
            $ship->getUserId(),
            $ship->getTraktorShip()->getUserId(),
            sprintf(_('Der Fluchtversuch der %s ist gescheitert'), $ship->getName()),
            $ship->getTraktorShip()->isBase() ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );
    }

    private function sufferHullDamage(ShipInterface $ship, $game): void
    {
        $otherShip = $ship->getTraktorShip();
        $otherUserId = $otherShip->getUser()->getId();
        $shipName = $ship->getName();

        $game->addInformation(_('Der Fluchtversuch ist fehlgeschlagen:'));

        $damageMsg = $this->applyDamage->damage(new DamageWrapper((int) ceil($ship->getMaxHuell() * rand(10, 25) / 100)), $ship);
        $game->addInformationMergeDown($damageMsg);

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $otherShip->getId());

        if ($ship->getIsDestroyed()) {
            $this->entryCreator->addShipEntry(
                'Die ' . $shipName . ' (' . $ship->getRump()->getName() . ') wurde bei einem Fluchtversuch in Sektor ' . $ship->getSectorString() . ' zerstört',
                $ship->getUser()->getId()
            );

            $destroyMsg = $this->shipRemover->destroy($ship);
            if ($destroyMsg !== null) {
                $game->addInformation($destroyMsg);
            }

            $this->privateMessageSender->send(
                $ship->getUser()->getId(),
                $otherUserId,
                sprintf(_('Die %s wurde beim Fluchtversuch zerstört'), $shipName),
                $otherShip->isBase() ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
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
