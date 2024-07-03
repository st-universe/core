<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

/**
 * Creates an emergency call for a ship
 */
final class StartEmergency implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_EMERGENCY';

    public const int CHARACTER_LIMIT = 250;

    public function __construct(private ShipLoaderInterface $shipLoader, private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository, private StartEmergencyRequestInterface $startEmergencyRequest)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $this->startEmergencyRequest->getShipId(),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();

        // stop if emergency call is already active
        if ($ship->getIsInEmergency() === true) {
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
        $ship->setIsInEmergency(true);

        $game->addInformation('Das Notrufsignal wurde gestartet');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
