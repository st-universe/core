<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowShipCommunication;

use JBBCode\Parser;
use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Action\StartEmergency\StartEmergency;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

final class ShowShipCommunication implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SPACECRAFT_COMMUNICATION';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository,
        private Parser $bbCodeParser
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->setPageTitle(_('Schiffskommunikation'));
        $game->setMacroInAjaxWindow('html/ship/shipcommunication.twig');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'TEMPLATETEXT',
            sprintf(
                'Die %s in Sektor %s sendet folgende Broadcast Nachricht:',
                $this->bbCodeParser->parse($ship->getName())->getAsText(),
                $ship->getSectorString()
            )
        );

        if ($ship->getIsInEmergency() === true) {
            $emergency = $this->spacecraftEmergencyRepository->getByShipId($ship->getId());

            if ($emergency !== null) {
                $game->setTemplateVar('EMERGENCYTEXT', $emergency->getText());
            }
        }
        $game->setTemplateVar('EMERGENCYTEXTLIMIT', StartEmergency::CHARACTER_LIMIT);
    }
}
