<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AwayMission;

use request;
use Stu\Component\Quest\AwayMission\AwayMissionAccess;
use Stu\Component\Quest\AwayMission\AwayMissionService;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowAwayMission\ShowAwayMission;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\AwayMission as Mission;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class AwayMission implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_AWAY_MISSION';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private AwayMissionAccess $missionAccess,
        private AwayMissionService $missionService,
        private AwayMissionRepositoryInterface $missionRepository,
        private PlanetFieldRepositoryInterface $fieldRepository,
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowAwayMission::VIEW_IDENTIFIER);
        if (!request::isPost()) {
            return;
        }

        $ship = $this->shipLoader->getByIdAndUser(request::indInt('id'), $context->getUser()->getId());
        $mission = $this->missionAccess->getMission($ship, $context->getInfo());
        if ($mission === null) {
            return;
        }

        $progress = $this->missionRepository->getProgress($mission, $context->getUser(), true);
        if ($progress === null) {
            $context->getInfo()->addInformation('Scanne zuerst die Oberfläche');
            return;
        }

        $action = request::postString('mission_action');
        if (!$this->missionService->checkCrew($ship, $context->getInfo())) {
            return;
        }

        $factionId = $context->getUser()->getFactionId();
        if ($action === 'scan') {
            $this->scanField($mission, $progress, $context, $factionId);

        } elseif ($action === 'page') {
            $this->changePage($mission, $progress, $factionId);
        } elseif ($action === 'reward') {
            $this->claimReward($mission, $progress, $ship, $context, $factionId);
        }

        $this->missionRepository->saveProgress($progress);
    }

    private function scanField(Mission $mission, AwayMissionProgress $progress, ActionControllerContext $context, int $factionId): void
    {
        $scanPage = $this->missionService->getScanPage($mission, $factionId);
        if (!$progress->isStarted() || $scanPage === null || $progress->getLastPage() < $scanPage) {
            $context->getInfo()->addInformation('Folge zuerst dem Missionsbericht bis zum Oberflächenscan');
            return;
        }
        $fieldId = request::postInt('field');
        if (!in_array($fieldId, $this->missionService->getFieldIds($mission), true)
            || $this->fieldRepository->getByColonyAndFieldIndex($mission->getColony()->getId(), $fieldId) === null) {
            $context->getInfo()->addInformation('Der Scan dieses Feldes liefert keine weiteren Erkenntnisse');
            return;
        }

        $progress->scanField($fieldId);
    }

    private function changePage(Mission $mission, AwayMissionProgress $progress, int $factionId): void
    {
        $page = request::postInt('mission_page');
        $pageCount = count($this->missionService->getPages($mission, $factionId));
        if (!$progress->isStarted() || $page < 1 || $page > $pageCount || $page > $progress->getLastPage() + 1
            || !$this->missionService->canVisitPage($mission, $progress, $page)) {
            return;
        }

        $progress->setLastPage(max($progress->getLastPage(), $page));
    }

    private function claimReward(Mission $mission, AwayMissionProgress $progress, Ship $ship, ActionControllerContext $context, int $factionId): void
    {
        $pageCount = count($this->missionService->getPages($mission, $factionId));
        if (!$progress->isStarted() || $pageCount === 0 || $progress->getLastPage() < $pageCount
            || !$this->missionService->isScanComplete($mission, $progress)) {
            $context->getInfo()->addInformation('Schließe zuerst die Außenmission ab');
            return;
        }
        if ($progress->isRewardReceived()) {
            $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            return;
        }
        $commodity = $mission->getRewardCommodity();
        $amount = $mission->getRewardAmount();
        if ($commodity === null || $amount <= 0) {
            $context->getInfo()->addInformation('Für diese Außenmission ist keine gültige Belohnung hinterlegt');
            return;
        }
        $researchCommodity = $this->getResearchCommodity($progress);
        if ($researchCommodity === null) {
            $context->getInfo()->addInformation('Für diese Außenmission ist kein gültiger Forschungsdatensatz hinterlegt');
            return;
        }
        $requiredSpace = $amount + 1;
        if ($ship->getMaxStorage() - $ship->getStorageSum() < $requiredSpace) {
            $context->getInfo()->addInformationf('Für die Belohnung werden %d freie Lagereinheiten benötigt', $requiredSpace);
            return;
        }

        $this->storageManager->upperStorage($ship, $commodity, $amount);
        $this->storageManager->upperStorage($ship, $researchCommodity, 1);
        $progress->setRewardReceived(true);
        $context->getInfo()->addInformationf('%d %s wurden dem Lagerraum hinzugefügt', $amount, $commodity->getName());
        $context->getInfo()->addInformationf('1 %s wurde dem Lagerraum hinzugefügt', $researchCommodity->getName());
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    private function getResearchCommodity(AwayMissionProgress $progress): ?Commodity
    {
        $system = $progress->getResearchSystem();

        return $system === null ? null : $this->commodityRepository->find($system->getCommodityId());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
