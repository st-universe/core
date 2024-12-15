<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowColonyScan;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\MatrixScannerShipSystem;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyScanRepositoryInterface;

final class ShowColonyScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONY_SCAN';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ColonyScanRepositoryInterface $colonyScanRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Kolonie scannen'));

        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            return;
        }

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            throw new SanityCheckException('ship is not in system');
        }

        $colony = $starsystemMap->getColony();
        if ($colony === null) {
            throw new SanityCheckException('ship is not over colony');
        }

        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::SYSTEM_MATRIX_SCANNER)) {
            throw new SanityCheckException('matrix scanner is not healthy');
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            throw new SanityCheckException('ship has no eps system installed');
        }


        if ($epsSystem->getEps() < MatrixScannerShipSystem::SCAN_EPS_COST) {
            $game->addInformation(sprintf(_('Aktion nicht möglich, ungenügend Energie vorhanden. Bedarf: %dE'), MatrixScannerShipSystem::SCAN_EPS_COST));
            $game->setMacroInAjaxWindow('');
            return;
        }

        $epsSystem->lowerEps(MatrixScannerShipSystem::SCAN_EPS_COST)->update();
        $this->spacecraftLoader->save($ship);

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $colony->getUserId(),
            sprintf(_('Der Spieler %s hat die Oberfläche deiner Kolonie %s gescannt.'), $game->getUser()->getName(), $colony->getName()),
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );

        $colonySurface = $this->colonyLibFactory->createColonySurface($colony, null, false);
        $colonySurface->updateSurface();

        $game->setTemplateVar('currentColony', $colony);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('DEPOSITS', $colony->getColonyClass()->getColonyClassDeposits());
        $game->setTemplateVar('SURFACE', $colonySurface);

        $this->createColonyScan($game->getUser(), $colony);
        $game->setMacroInAjaxWindow('html/ship/colonyscan.twig');
    }

    private function createColonyScan(UserInterface $user, ColonyInterface $colony): void
    {
        $colonyscan = $this->colonyScanRepository->prototype();
        $colonyscan->setColony($colony);
        $colonyscan->setUser($user);
        $colonyscan->setColonyUserId($colony->getUserId());
        $colonyscan->setColonyName($colony->getName());
        $colonyscan->setColonyUserName($colony->getUser()->getName());
        $colonyscan->setFieldData(serialize($this->colonyScanRepository->getSurface($colony->getId())));
        $colonyscan->setDate(time());

        $this->colonyScanRepository->save($colonyscan);
    }
}
