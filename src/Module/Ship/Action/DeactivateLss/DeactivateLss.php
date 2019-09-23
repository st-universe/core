<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateLss;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DeactivateLss implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_LSS';

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
        $ship->setLss(false);

        $this->shipRepository->save($ship);

        $game->addInformation("Langstreckensensoren deaktiviert");
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
