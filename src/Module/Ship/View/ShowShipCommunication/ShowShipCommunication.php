<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShipCommunication;

use Override;
use JBBCode\Parser;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Action\StartEmergency\StartEmergency;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;

final class ShowShipCommunication implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_COMMUNICATION';

    public function __construct(private ShipLoaderInterface $shipLoader, private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository, private Parser $bbCodeParser)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->setPageTitle(_('Schiffskommunikation'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/shipcommunication');

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
