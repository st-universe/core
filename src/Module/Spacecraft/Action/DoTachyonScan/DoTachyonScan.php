<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DoTachyonScan;

use Override;
use request;

use Stu\Component\Spacecraft\System\Type\TachyonScannerShipSystem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;

final class DoTachyonScan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TACHYON_SCAN';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private TachyonScanRepositoryInterface $tachyonScanRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );
        $ship = $wrapper->get();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        // scanner needs to be present
        if (!$ship->hasTachyonScanner()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, kein Tachyon-Scanner installiert![/color][/b]'));
            return;
        }

        // scanner needs to be active
        if (!$ship->getTachyonState()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, der Tachyon-Scanner muss aktiviert sein![/color][/b]'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        // scanner needs to be active
        if ($epsSystem === null || $epsSystem->getEps() < TachyonScannerShipSystem::SCAN_EPS_COST) {
            $game->addInformation(sprintf(_('[b][color=#ff2626]Aktion nicht möglich, ungenügend Energie vorhanden. Bedarf: %dE[/color][/b]'), TachyonScannerShipSystem::SCAN_EPS_COST));
            return;
        }

        $tachyonScan = $this->tachyonScanRepository->prototype();
        $tachyonScan->setUser($ship->getUser());
        $tachyonScan->setLocation($ship->getLocation());
        $tachyonScan->setScanTime(time());

        $this->tachyonScanRepository->save($tachyonScan);

        $epsSystem->lowerEps(TachyonScannerShipSystem::SCAN_EPS_COST)->update();
        $this->spacecraftRepository->save($ship);

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::TACHYON_SCAN_JUST_HAPPENED, true);
        $game->addInformation("Der umfangreiche Tachyon-Scan wurde durchgeführt");
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
