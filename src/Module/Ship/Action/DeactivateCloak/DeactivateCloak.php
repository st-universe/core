<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateCloak;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DeactivateCloak implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_CLOAK';

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

        $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_CLOAK);

        $this->shipRepository->save($ship);

        // @todo redalert

        $game->addInformation("Tarnung deaktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
