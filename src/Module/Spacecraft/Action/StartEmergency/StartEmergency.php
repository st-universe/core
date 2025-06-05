<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StartEmergency;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

/**
 * Creates an emergency call for a spacecraft
 */
final class StartEmergency implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_EMERGENCY';

    public const int CHARACTER_LIMIT = 250;

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository,
        private StartEmergencyRequestInterface $startEmergencyRequest
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $this->startEmergencyRequest->getShipId(),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();

        // stop if emergency call is already active
        if ($wrapper->getComputerSystemDataMandatory()->isInEmergency()) {
            return;
        }

        $text = $this->startEmergencyRequest->getEmergencyText();

        if (mb_strlen($text) > self::CHARACTER_LIMIT) {
            $game->addInformationf('Maximal %d Zeichen erlaubt', self::CHARACTER_LIMIT);
            return;
        }

        $emergency = $this->spacecraftEmergencyRepository->prototype();
        $emergency->setSpacecraft($ship);
        $emergency->setText($text);
        $emergency->setDate(time());
        $this->spacecraftEmergencyRepository->save($emergency);
        $wrapper->getComputerSystemDataMandatory()->setIsInEmergency(true)->update();

        $game->addInformation('Das Notrufsignal wurde gestartet');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
