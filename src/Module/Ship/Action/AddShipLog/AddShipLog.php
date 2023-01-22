<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AddShipLog;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipLogRepositoryInterface;

final class AddShipLog implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_SHIP_LOG';

    private ShipLoaderInterface $shipLoader;

    private ShipLogRepositoryInterface $shipLogRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipLogRepositoryInterface $shipLogRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipLogRepository = $shipLogRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $text = request::postStringFatal('log');

        $shipLog = $this->shipLogRepository->prototype();
        $shipLog->setShip($ship);
        $shipLog->setText($text);
        $shipLog->setDate(time());

        $this->shipLogRepository->save($shipLog);
        $ship->getLogbook()->add($shipLog);

        $game->setView(ShowShip::VIEW_IDENTIFIER);
        $game->addInformation('Logbucheintrag wurde hinzugefügt');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
