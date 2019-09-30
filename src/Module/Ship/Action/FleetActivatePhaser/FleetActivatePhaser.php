<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivatePhaser;

use request;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetActivatePhaser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_PHASER';

    private $shipLoader;

    private $shipRepository;

    private $shipSystemManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Energiewaffe";
        foreach ($ship->getFleet()->getShips() as $ship) {
            $error = null;
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_PHASER);
            } catch (InsufficientEnergyException $e) {
                $error = _('Nicht genügend Energie zur Aktivierung vorhanden');
            } catch (SystemDamagedException $e) {
                $error = _('Die Energiewaffe ist beschädigt und kann nicht aktiviert werden');
            } catch (ShipSystemException $e) {
                $error = _('Die Energiewaffe konnte nicht aktiviert werden');
            } finally {
                if ($error !== null) {

                    $msg[] = sprintf(
                        '%s: %s',
                        $ship->getName(),
                        $error
                    );
                } else {
                    $this->shipRepository->save($ship);
                }
            }
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
