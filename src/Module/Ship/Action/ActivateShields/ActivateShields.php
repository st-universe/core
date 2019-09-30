<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateShields;

use request;
use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ActivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_SHIELDS';

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

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        try {
            $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);

            $this->shipRepository->save($ship);
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(_('Nicht genügend Energie zur Aktivierung vorhanden'));
            return;
        } catch (SystemDamagedException $e) {
            $game->addInformation(_('Die Schilde konnten aufgrund beschädigter Schildemitter nicht aktiviert werden'));
            return;
        } catch (ActivationConditionsNotMetException $e) {
            $game->addInformation(_('Die Schilde konnten nicht aktiviert werden'));
            return;
        }

        $game->addInformation("Schilde aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
