<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivatePhaser;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;
use SystemActivationWrapper;

final class ActivatePhaser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_PHASER';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        if (!$ship->hasPhaser() || $ship->getPhaser()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->setPhaser(true);

        $this->shipRepository->save($ship);

        $game->addInformation("Strahlenwaffe aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
