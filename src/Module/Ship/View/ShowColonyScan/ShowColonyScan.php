<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowColonyScan;

use request;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\MatrixScannerShipSystem;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowColonyScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY_SCAN';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyRepositoryInterface $colonyRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyRepositoryInterface $colonyRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyRepository = $colonyRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('ERROR', true);

        $game->setPageTitle(_('Kolonie scannen'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/colonyscan');

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            return;
        }

        $colony = $this->colonyRepository->getByPosition(
            $ship->getStarsystemMap()
        );

        if ($colony === null) {
            return;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER)) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem->getEps() < MatrixScannerShipSystem::SCAN_EPS_COST) {
            $game->addInformation(sprintf(_('Aktion nicht möglich, ungenügend Energie vorhanden. Bedarf: %dE'), MatrixScannerShipSystem::SCAN_EPS_COST));
            return;
        }

        $epsSystem->setEps($epsSystem->getEps() - MatrixScannerShipSystem::SCAN_EPS_COST)->update();
        $this->shipRepository->save($ship);

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int) $colony->getUserId(),
            sprintf(_('Der Spieler %s hat die Oberfläche deiner Kolonie %s gescannt.'), $game->getUser()->getUserName(), $colony->getName()),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
        );

        $game->setTemplateVar('currentColony', $colony);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('DEPOSITS', $colony->getColonyClass()->getColonyClassDeposits());
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony, null, false));
        $game->setTemplateVar('ERROR', false);
    }
}
