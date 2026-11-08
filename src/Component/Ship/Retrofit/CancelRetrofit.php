<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Retrofit;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\ModuleScreen\ModuleQualitySelection;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class CancelRetrofit implements CancelRetrofitInterface
{
    public function __construct(
        private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private readonly StorageManagerInterface $storageManager,
        private readonly PrivateMessageSenderInterface $privateMessageSender
    ) {}


    #[Override]
    public function cancelRetrofit(Ship $ship): bool
    {
        $state = $ship->getState();
        if ($state === SpacecraftStateEnum::RETROFIT) {
            $queueEntry = $this->colonyShipQueueRepository->getByShip($ship->getId());
            if ($queueEntry === null) {
                $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
                return true;
            }

            $colony = $queueEntry->getColony();
            $newBuildplan = $queueEntry->getSpacecraftBuildplan();
            $oldBuildplan = $ship->getBuildplan();
            $returnedmodules = [];

            $buildTime = $queueEntry->getBuildtime();
            $finishDate = $queueEntry->getFinishDate();
            $startDate = $finishDate - $buildTime;
            $currentTime = time();
            $firstQuarter = $startDate + ($buildTime * 0.25);

            $withinFirstQuarter = $currentTime <= $firstQuarter;


            if ($oldBuildplan != null && $newBuildplan != null) {
                foreach (SpacecraftModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {
                    $newModules = $newBuildplan->getModulesByType($moduleType)->toArray();

                    /** @var array<Module> */
                    $addingModules = $this->getNewModules($newModules, $oldBuildplan, $newBuildplan);

                    if ($withinFirstQuarter) {
                        foreach ($addingModules as $module) {
                            if ($module->getType() != SpacecraftModuleTypeEnum::HULL) {
                                $returnedmodules[] = ['module' => $module, 'steps' => $newBuildplan->getModuleImprovementSteps($module)];
                            }
                        }
                    }
                }
            }

            $txt = _("Auf der Kolonie " . $colony->getName() . " wurde die Umrüstung der " . $ship->getName() . " abgebrochen");

            if ($returnedmodules !== []) {
                $msg = "\n\nDie folgenden Module wurden beim Abbruch der Umrüstung zurückgewonnen: \n";

                foreach ($returnedmodules as $entry) {
                    $this->storageManager->addStorage($colony, $entry['module']->getCommodity(), [$entry['steps'] => 1]);
                    $msg .= "- " . $entry['module']->getName() . "\n";
                }

                $txt .= $msg;
            } else {
                $txt .= "\n\nEs konnten keine Module zurückgewonnen werden, da der Umrüstungsprozess bereits zu weit fortgeschritten war";
            }

            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $colony->getUserId(),
                $txt,
                PrivateMessageFolderTypeEnum::SPECIAL_COLONY
            );

            $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
            $this->colonyShipQueueRepository->truncateByShip($ship->getId());

            return true;
        }

        return false;
    }
    /**
     * @param array<Module> $modules
     * @return array<Module>
     */
    private function getNewModules(array $modules, SpacecraftBuildplan $oldPlan, SpacecraftBuildplan $newPlan): array
    {
        return array_filter($modules, fn (Module $module): bool => !ModuleQualitySelection::matchesPlan($module, $oldPlan, $newPlan->getModuleImprovementSteps($module)));
    }
}
