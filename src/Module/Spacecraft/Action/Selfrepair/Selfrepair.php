<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\Selfrepair;

use request;
use RuntimeException;
use Stu\Component\Spacecraft\Repair\RepairTaskConstants;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;

final class Selfrepair implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SELF_REPAIR';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private RepairUtilInterface $repairUtil,
        private StorageManagerInterface $storageManager
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $context->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(request::postIntFatal('id'), $userId);

        $ship = $wrapper->get();

        if (!$wrapper->isUnalerted()) {
            return;
        }

        $repairType = request::postInt('partschoice');
        $sid = request::postInt('sid');

        if ($repairType === 0) {
            $context->getInfo()->addInformation(_('Es muss ausgewählt werden, welche Teile verwenden werden sollen.'));
        }

        if ($sid === 0) {
            $context->getInfo()->addInformation(_('Es muss ausgewählt werden, welches System repariert werden soll.'));
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

        if (!$ship->hasEnoughCrew($context)) {
            return;
        }

        if ($ship->getCondition()->isUnderRepair()) {
            $context->getInfo()->addInformation(_('Das Schiff wird bereits repariert.'));
            return;
        }

        if ($ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING) {
            $context->getInfo()->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $neededSparePartCount = (int) ($ship->getMaxHull() / 150);


        if ($isInstantRepair === false) {
            if (!$this->checkForSpareParts($ship, $neededSparePartCount, $repairType, $context)) {
                return;
            }

            $ship->getCondition()->setState(SpacecraftStateEnum::REPAIR_ACTIVE);

            $freeEngineerCount = $this->repairUtil->determineFreeEngineerCount($ship);
            $duration = RepairTaskConstants::STANDARD_REPAIR_DURATION * (1 - $freeEngineerCount / 10);

            $this->consumeCommodities($ship, $repairType, $neededSparePartCount, $context);
            $this->repairUtil->createRepairTask($ship, $systemType, $repairType, time() + (int) $duration);
            $context->getInfo()->addInformationf(
                _('Das Schiffssystem %s wird repariert. Fertigstellung %s'),
                $systemType->getDescription(),
                date("d.m.Y H:i", (time() + (int) $duration))
            );
        } else {
            if (!$this->checkForSpareParts($ship, 3 * $neededSparePartCount, $repairType, $context)) {
                return;
            }

            $this->consumeCommodities($ship, $repairType, 3 * $neededSparePartCount, $context);
            $healingPercentage = $this->repairUtil->determineHealingPercentage($repairType);
            $isSuccess = $this->repairUtil->instantSelfRepair($ship, $systemType, $healingPercentage);

            if ($isSuccess) {
                $context->getInfo()->addInformationf(
                    _('Die Crew hat das System %s auf %d %% reparieren können'),
                    $systemType->getDescription(),
                    $healingPercentage
                );
            } else {
                $context->getInfo()->addInformationf(
                    _('Der Reparaturversuch des Systems %s brachte keine Besserung'),
                    $systemType->getDescription(),
                    $ship->getName()
                );
            }
        }
    }

    private function checkForSpareParts(Spacecraft $ship, int $neededSparePartCount, int $repairType, ActionControllerContext $context): bool
    {
        $result = true;

        if (
            ($repairType === RepairTaskConstants::SPARE_PARTS_ONLY || $repairType === RepairTaskConstants::BOTH)
            && ($ship->getStorage()->get(CommodityTypeConstants::COMMODITY_SPARE_PART)?->getAmount() ?? 0) < $neededSparePartCount
        ) {
            $context->getInfo()->addInformationf(_('Für die Reparatur werden %d Ersatzteile benötigt'), $neededSparePartCount);
            $result = false;
        }

        if (
            ($repairType === RepairTaskConstants::SYSTEM_COMPONENTS_ONLY || $repairType === RepairTaskConstants::BOTH)
            && ($ship->getStorage()->get(CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT)?->getAmount() ?? 0) < $neededSparePartCount
        ) {
            $context->getInfo()->addInformationf(_('Für die Reparatur werden %d Systemkomponenten benötigt'), $neededSparePartCount);
            $result = false;
        }

        return $result;
    }

    private function consumeCommodities(Spacecraft $ship, int $repairType, int $neededSparePartCount, ActionControllerContext $context): void
    {
        if (
            $repairType === RepairTaskConstants::SPARE_PARTS_ONLY
            || $repairType === RepairTaskConstants::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeConstants::COMMODITY_SPARE_PART)?->getCommodity() ?? throw new RuntimeException('this should not happen');
            $this->storageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
            $context->getInfo()->addInformationf(_('Für die Reparatur werden %d Ersatzteile verwendet'), $neededSparePartCount);
        }

        if (
            $repairType === RepairTaskConstants::SYSTEM_COMPONENTS_ONLY
            || $repairType === RepairTaskConstants::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT)?->getCommodity() ?? throw new RuntimeException('this should not happen');
            $this->storageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
            $context->getInfo()->addInformationf(_('Für die Reparatur werden %d Systemkomponenten verwendet'), $neededSparePartCount);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
