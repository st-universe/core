<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowAwayMission;

use request;
use Stu\Component\Quest\AwayMission\AwayMissionAccess;
use Stu\Component\Quest\AwayMission\AwayMissionService;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;

final class ShowAwayMission implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_AWAY_MISSION';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private AwayMissionAccess $missionAccess,
        private AwayMissionService $missionService,
        private AwayMissionRepositoryInterface $missionRepository,
        private ColonyLibFactoryInterface $colonyLibFactory
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setMacroInAjaxWindow('');
        $ship = $this->shipLoader->getByIdAndUser(request::indInt('id'), $game->getUser()->getId());
        $mission = $this->missionAccess->getMission($ship, $game->getInfo());
        if ($mission === null) {
            return;
        }
        $progress = $this->missionRepository->getProgress($mission, $game->getUser(), true);
        if ($progress === null) {
            $game->getInfo()->addInformation('Scanne zuerst die Oberfläche');
            return;
        }

        $this->render($mission, $progress, $ship, $game);
    }

    public function render(AwayMission $mission, AwayMissionProgress $progress, Ship $ship, ViewControllerContext $game): void
    {
        $game->setPageTitle($mission->getTitle());
        if (!$this->prepareProgress($progress, $ship, $game)) {
            return;
        }
        $factionId = $ship->getUser()->getFactionId();
        $pages = $this->missionService->getPages($mission, $factionId);
        $pageCount = count($pages);
        $page = max(1, min(request::indInt('mission_page') ?: $progress->getLastPage(), $progress->getLastPage(), max(1, $pageCount)));
        $scanPage = $this->missionService->getScanPage($mission, $factionId);
        if (!$this->missionService->canVisitPage($mission, $progress, $page) && $scanPage !== null) {
            $page = $scanPage;
        }
        $showSurface = $scanPage === $page;
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('AWAY_MISSION', $mission);
        $game->setTemplateVar('MISSION_PROGRESS', $progress);
        $game->setTemplateVar('MISSION_DIALOGUE', !$showSurface);
        $game->setTemplateVar('MISSION_PAGE', $page);
        $game->setTemplateVar('MISSION_PAGE_COUNT', $pageCount);
        $game->setTemplateVar('MISSION_CAN_CONTINUE', $page < $pageCount && $this->missionService->canVisitPage($mission, $progress, $page + 1));
        $game->setTemplateVar('MISSION_ENTRIES', $this->missionService->renderPage($mission, $ship, $page, $progress));
        if ($showSurface) {
            $this->showSurface($mission, $progress, $game);
        }
        $game->setMacroInAjaxWindow('html/ship/awayMission.twig');
    }

    private function prepareProgress(AwayMissionProgress $progress, Ship $ship, ViewControllerContext $game): bool
    {
        if (!$this->missionService->hasRequiredCrew($ship)) {
            if ($game->getInfo()->isEmpty()) {
                $this->missionService->checkCrew($ship, $game->getInfo());
            }
            $game->setMacroInAjaxWindow('');
            return false;
        }
        $this->missionService->prepareProgress($progress, $ship);
        $this->missionRepository->saveProgress($progress);

        return true;
    }

    private function showSurface(AwayMission $mission, AwayMissionProgress $progress, ViewControllerContext $game): void
    {
        $surface = $this->colonyLibFactory->createColonySurface($mission->getColony(), null, false);
        $surface->updateSurface();
        $game->setTemplateVar('SURFACE', $surface);
        $game->setTemplateVar('SCAN_COUNT', $this->missionService->getScannedFieldCount($mission, $progress));
        $game->setTemplateVar('SCAN_TOTAL', count($this->missionService->getFieldIds($mission)));
    }
}
