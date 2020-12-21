<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateWarp;

use request;
use Stu\Component\Ship\System\Exception\AlreadyActiveException;
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

final class FleetActivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_WARP';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

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

        $msg = [];
        $msg[] = _('Flottenbefehl ausgeführt: Aktivierung des Warpantriebs');
        foreach ($ship->getFleet()->getShips() as $ship) {
            $error = null;
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            } catch (AlreadyActiveException $e) {
                $error = _('Der Warpantrieb ist bereits aktiviert');
            } catch (InsufficientEnergyException $e) {
                $error = _('Nicht genügend Energie zur Aktivierung vorhanden');
            } catch (SystemDamagedException $e) {
                $error = _('Der Warpantrieb ist beschädigt und konnte nicht aktiviert werden');
            } catch (ShipSystemException $e) {
                $error = _('Der Warpantrieb konnte nicht aktiviert werden');
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
