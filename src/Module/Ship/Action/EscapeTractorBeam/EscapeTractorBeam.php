<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EscapeTractorBeam;

use request;

use Stu\Lib\DamageWrapper;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class EscapeTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ESCAPE_TRAKTOR';

    private ShipLoaderInterface $shipLoader;
    
    private ApplyDamageInterface $applyDamage;
    
    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;
    
    private ShipRemoverInterface $shipRemover;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ApplyDamageInterface $applyDamage,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover
    ) {
        $this->shipLoader = $shipLoader;
        $this->applyDamage = $applyDamage;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        // is ship trapped in tractor beam?
        if ($ship->getTraktormode() != 2)
        {
            return;
        }

        //is deflector working?
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR))
        {
            return;
        }

        //enough energy?
        if ($ship->getEps() < 20)
        {
            return;
        }

        //eps cost
        $ship->setEps($ship->getEps() - 20);

        // probabilities
        $chance = rand(1,100);
        if ($chance < 11)
        {
            $this->escape($ship, $game);
        } elseif ($chance < 55)
        {
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
    
    private function escape($ship, $game): void
    {
        $otherShip = $ship->getTraktorShip();

        $otherShip->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setStatus(0);
        $otherShip->deactivateTraktorBeam();
        
        $this->shipRepository->save($otherShip);

        $this->privateMessageSender->send(
            (int)$ship->getUserId(),
            (int)$otherShip->getUserId(),
            sprintf(_('Bei dem Fluchtversuch der %s wurde der Traktorstrahl der %s in Sektor %s zerstört'), $ship->getName(), $otherShip->getName(), $ship->getSectorString()),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        $game->addInformation(_('Der Fluchtversuch ist gelungen'));
    }

    private function sufferDeflectorDamage($ship, GameControllerInterface $game): void
    {
        $msg = [];
        $msg[] = _('Der Fluchtversuch ist fehlgeschlagen:');

        $system = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_DEFLECTOR);
        $this->applyDamage->damageShipSystem($ship, $system, rand(5,25), $msg);

        $game->addInformationMergeDown($msg);

        $this->privateMessageSender->send(
            (int)$ship->getUserId(),
            (int)$ship->getTraktorShip()->getUserId(),
            sprintf(_('Der Fluchtversuch der %s ist gescheitert'), $ship->getName()),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
    }

    private function sufferHullDamage($ship, $game): void
    {
        $game->addInformation(_('Der Fluchtversuch ist fehlgeschlagen:'));
        
        $damageMsg = $this->applyDamage->damage(new DamageWrapper((int)ceil($ship->getMaxHuell() * rand(10,25) / 100)), $ship);
        $game->addInformationMergeDown($damageMsg);
        
        if ($ship->getIsDestroyed())
        {
            $destroyMsg = $this->shipRemover->destroy($ship);
            if ($destroyMsg !== null)
            {
                $game->addInformation($destroyMsg);
            }
            
            $this->privateMessageSender->send(
                (int)$ship->getUserId(),
                (int)$ship->getTraktorShip()->getUserId(),
                sprintf(_('Die %s wurde beim Fluchtversuch zerstört'), $ship->getName()),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        } else {

            $this->privateMessageSender->send(
                (int)$ship->getUserId(),
                (int)$ship->getTraktorShip()->getUserId(),
                sprintf(_('Der Fluchtversuch der %s ist gescheitert'), $ship->getName()),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
