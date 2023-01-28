<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

/**
 * Creates an emergency call for a ship
 */
final class StartEmergency implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_START_EMERGENCY';

    public const CHARACTER_LIMIT = 250;

    private ShipLoaderInterface $shipLoader;

    private ShipStateChangerInterface $shipStateChanger;

    private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository;

    private StartEmergencyRequestInterface $startEmergencyRequest;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStateChangerInterface $shipStateChanger,
        SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository,
        StartEmergencyRequestInterface $startEmergencyRequest
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStateChanger = $shipStateChanger;
        $this->spacecraftEmergencyRepository = $spacecraftEmergencyRepository;
        $this->startEmergencyRequest = $startEmergencyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $this->startEmergencyRequest->getShipId(),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();

        // stop if emergency call is already active
        if ($ship->isInEmergency()) {
            return;
        }

        $text = $this->startEmergencyRequest->getEmergencyText();

        if (mb_strlen($text) > self::CHARACTER_LIMIT) {
            $game->addInformationf('Maximal %d Zeichen erlaubt', self::CHARACTER_LIMIT);
            return;
        }

        $emergency = $this->spacecraftEmergencyRepository->prototype();
        $emergency->setShip($ship);
        $emergency->setText($text);
        $emergency->setDate(time());
        $this->spacecraftEmergencyRepository->save($emergency);

        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_EMERGENCY);

        $game->addInformation('Das Notrufsignal wurde gestartet');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
