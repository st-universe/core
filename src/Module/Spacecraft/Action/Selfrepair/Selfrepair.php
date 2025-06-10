<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\Selfrepair;

use Override;
use request;
use Stu\Component\Spacecraft\Repair\RepairTaskConstants;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class Selfrepair implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SELF_REPAIR';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private RepairUtilInterface $repairUtil,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private StorageManagerInterface $storageManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(request::postIntFatal('id'), $userId);

        $ship = $wrapper->get();

        if (!$wrapper->isUnalerted()) {
            return;
        }

        $repairType = request::postInt('partschoice');
        $sid = request::postInt('sid');

        if ($repairType === 0) {
            $game->addInformation(_('Es muss ausgewählt werden, welche Teile verwenden werden sollen.'));
        }

        if ($sid === 0) {
            $game->addInformation(_('Es muss ausgewählt werden, welches System repariert werden soll.'));
        }

        $systemType = SpacecraftSystemTypeEnum::from($sid);

        if ($repairType < RepairTaskConstants::SPARE_PARTS_ONLY || $repairType > RepairTaskConstants::BOTH) {
            return;
        }

        $repairOptions = $this->repairUtil->determineRepairOptions($wrapper);
        if (!array_key_exists($systemType->value, $repairOptions)) {
            return;
        }

        $isInstantRepair = request::postString('instantrepair');

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->getCondition()->isUnderRepair()) {
            $game->addInformation(_('Das Schiff wird bereits repariert.'));
            return;
        }

        if ($ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING) {
            $game->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $neededSparePartCount = (int) ($ship->getMaxHull() / 150);


        if ($isInstantRepair === false) {
            if (!$this->checkForSpareParts($ship, $neededSparePartCount, $repairType, $game)) {
                return;
            }

            $ship->getCondition()->setState(SpacecraftStateEnum::REPAIR_ACTIVE);

            $freeEngineerCount = $this->repairUtil->determineFreeEngineerCount($ship);
            $duration = RepairTaskConstants::STANDARD_REPAIR_DURATION * (1 - $freeEngineerCount / 10);

            $this->consumeCommodities($ship, $repairType, $neededSparePartCount, $game);
            $this->repairUtil->createRepairTask($ship, $systemType, $repairType, time() + (int) $duration);
            $game->addInformationf(
                _('Das Schiffssystem %s wird repariert. Fertigstellung %s'),
                $systemType->getDescription(),
                date("d.m.Y H:i", (time() + (int) $duration))
            );
        } else {
            if (!$this->checkForSpareParts($ship, 3 * $neededSparePartCount, $repairType, $game)) {
                return;
            }

            $this->consumeCommodities($ship, $repairType, 3 * $neededSparePartCount, $game);
            $healingPercentage = $this->repairUtil->determineHealingPercentage($repairType);
            $isSuccess = $this->repairUtil->instantSelfRepair($ship, $systemType, $healingPercentage);

            if ($isSuccess) {
                $game->addInformationf(
                    _('Die Crew hat das System %s auf %d %% reparieren können'),
                    $systemType->getDescription(),
                    $healingPercentage
                );
            } else {
                $game->addInformationf(
                    _('Der Reparaturversuch des Systems %s brachte keine Besserung'),
                    $systemType->getDescription(),
                    $ship->getName()
                );
            }
        }

        $this->spacecraftRepository->save($ship);
    }

    private function checkForSpareParts(SpacecraftInterface $ship, int $neededSparePartCount, int $repairType, GameControllerInterface $game): bool
    {
        $result = true;

        if (
            ($repairType === RepairTaskConstants::SPARE_PARTS_ONLY || $repairType === RepairTaskConstants::BOTH)
            && (!$ship->getStorage()->containsKey(CommodityTypeEnum::COMMODITY_SPARE_PART)
                || $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SPARE_PART)->getAmount() < $neededSparePartCount)
        ) {
            $game->addInformationf(_('Für die Reparatur werden %d Ersatzteile benötigt'), $neededSparePartCount);
            $result = false;
        }

        if (
            ($repairType === RepairTaskConstants::SYSTEM_COMPONENTS_ONLY || $repairType === RepairTaskConstants::BOTH)
            && (!$ship->getStorage()->containsKey(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT)
                || $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT)->getAmount() < $neededSparePartCount)
        ) {
            $game->addInformationf(_('Für die Reparatur werden %d Systemkomponenten benötigt'), $neededSparePartCount);
            $result = false;
        }

        return $result;
    }

    private function consumeCommodities(SpacecraftInterface $ship, int $repairType, int $neededSparePartCount, GameControllerInterface $game): void
    {
        if (
            $repairType === RepairTaskConstants::SPARE_PARTS_ONLY
            || $repairType === RepairTaskConstants::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SPARE_PART)->getCommodity();
            $this->storageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
            $game->addInformationf(_('Für die Reparatur werden %d Ersatzteile verwendet'), $neededSparePartCount);
        }

        if (
            $repairType === RepairTaskConstants::SYSTEM_COMPONENTS_ONLY
            || $repairType === RepairTaskConstants::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT)->getCommodity();
            $this->storageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
            $game->addInformationf(_('Für die Reparatur werden %d Systemkomponenten verwendet'), $neededSparePartCount);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
