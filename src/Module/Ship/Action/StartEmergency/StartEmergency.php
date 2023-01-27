<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

final class StartEmergency implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_START_EMERGENCY';

    private ShipLoaderInterface $shipLoader;

    private ShipStateChangerInterface $shipStateChanger;

    private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStateChangerInterface $shipStateChanger,
        SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStateChanger = $shipStateChanger;
        $this->spacecraftEmergencyRepository = $spacecraftEmergencyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();

        if ($ship->isInEmergency()) {
            return;
        }

        $text = request::postStringFatal('text');

        $emergency = $this->spacecraftEmergencyRepository->prototype();
        $emergency->setShip($ship);
        $emergency->setText($text);
        $emergency->setDate(time());
        $this->spacecraftEmergencyRepository->save($emergency);

        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_EMERGENCY);

        $game->addInformation(_("Das Notrufsignal wurde gestartet"));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
