<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateModules;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Research\ModuleProductionQuality;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class CreateModules implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_MODULES';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository,
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository,
        private ModuleProductionQuality $productionQuality
    ) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $context->getUser()->getId();

        $moduleAdded = false;

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $moduleIds = request::getArrayFatal('moduleids');
        $values = request::getArrayFatal('values');
        $optimizations = request::returnArray(request::getvars()['optimizations'] ?? null);
        $function = BuildingFunctionEnum::from(request::getIntFatal('func'));

        if ($this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony,
            [$function],
            [0, 1]
        ) === 0) {
            return;
        }
        $prod = [];

        /** @var ModuleBuildingFunction[] $modules_av */
        $modules_av = [];
        foreach ($this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser($function, $userId) as $module) {
            $modules_av[$module->getModuleId()] = $module;
        }

        $storage = $colony->getStorage();
        $changeable = $colony->getChangeable();
        $projectedStorageAmounts = [];
        foreach ($storage as $storageItem) {
            $projectedStorageAmounts[$storageItem->getCommodity()->getId()] = $storageItem->getAmount();
        }
        $projectedEps = $changeable->getEps();
        $failedModuleIds = [];
        $missingCommodityTotals = [];

        foreach ($moduleIds as $key => $moduleId) {
            if (!array_key_exists($moduleId, $modules_av)) {
                continue;
            }
            $count = (int)$values[$key];
            if ($count <= 0) {
                continue;
            }
            $isEnoughAvailable = false;
            $initialcount = $count;
            $module = $modules_av[$moduleId]->getModule();
            $moduleEcost = $module->getEcost();
            $availableEps = $changeable->getEps();
            if ($moduleEcost > 0 && $moduleEcost * $initialcount > $availableEps) {
                $count = (int) floor($availableEps / $moduleEcost);
            }
            $quality = $this->selectedQuality($context->getUser(), $module, $optimizations[$moduleId] ?? 0);
            $costs = $this->productionQuality->getCosts($module, $quality);

            $missingcount = 0;
            foreach ($costs as $cost) {
                $commodity = $cost->getCommodity();
                $commodityId = $commodity->getId();

                $stor = $storage[$commodityId] ?? null;
                $availableAmount = ($stor !== null) ? $stor->getAmount() : 0;

                if ($availableAmount < $cost->getAmount() * $initialcount) {
                    $missing = $initialcount - (int) floor($availableAmount / $cost->getAmount());
                    if ($missingcount < $missing) {
                        $missingcount = $missing;
                    }
                    if ($count > $initialcount - $missingcount) {
                        $count = $initialcount - $missingcount;
                    }
                }
            }
            if ($count > 0) {
                foreach ($costs as $cost) {
                    $this->storageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount() * $count);
                }
                $changeable->lowerEps($count * $moduleEcost);
                $this->lowerProjectedStorageAmounts($projectedStorageAmounts, $costs, $count);
                $projectedEps = max(0, $projectedEps - $count * $moduleEcost);

                $this->colonyRepository->save($colony);
                $this->queueModule($colony, $module, $function, $count, $quality);
                $moduleAdded = true;
            }
            if ($count === $initialcount) {
                $isEnoughAvailable = true;
            }

            $missing = $isEnoughAvailable ? '' : sprintf(_(' von %s'), $initialcount);

            $prod[] = $count . $missing . ' ' . $module->getName();

            if (!$isEnoughAvailable) {
                $failedModuleIds[(int) $moduleId] = true;
                $missingModuleCount = $initialcount - $count;
                $requiredMissingEps = $moduleEcost * $missingModuleCount;
                $missingEps = max(0, $requiredMissingEps - $projectedEps);
                $projectedEps = max(0, $projectedEps - $requiredMissingEps);
                $missingText = $missingEps > 0
                    ? [sprintf(_('%s Energie'), $missingEps)]
                    : [];

                $missingText = array_merge(
                    $missingText,
                    $this->formatMissingCommodities($this->getMissingCommodities(
                        $missingModuleCount,
                        $costs,
                        $projectedStorageAmounts,
                        $missingCommodityTotals
                    ))
                );

                $prod[] = sprintf(
                    _("\u{00A0}\u{00A0}\u{00A0}\u{00A0}- Zur Herstellung von weiteren %d %s werden benötigt: %s"),
                    $missingModuleCount,
                    $module->getName(),
                    implode(', ', $missingText)
                );
            }
        }

        if (count($failedModuleIds) > 1 && $missingCommodityTotals !== []) {
            ksort($missingCommodityTotals);

            $prod[] = sprintf(
                _("\u{00A0}\u{00A0}\u{00A0}\u{00A0}- Insgesamt werden zusätzlich benötigt: %s"),
                implode(', ', $this->formatMissingCommodities($missingCommodityTotals))
            );
        }

        if ($moduleAdded) {
            $context->getInfo()->addInformation(_('Es wurden folgende Module zur Warteschlange hinzugefügt:'));
            foreach ($prod as $msg) {
                $context->getInfo()->addInformation($msg);
            }
        } elseif ($prod !== []) {
            foreach ($prod as $msg) {
                $context->getInfo()->addInformation($msg);
            }
        } else {
            $context->getInfo()->addInformation(_('Es wurden keine Module hergestellt oder ausgewählt'));
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function selectedQuality(User $user, Module $module, mixed $selection): int
    {
        return in_array($selection, [1, '1'], true) ? $this->productionQuality->getImprovementSteps($user, $module) : 0;
    }

    private function queueModule(Colony $colony, Module $module, BuildingFunctionEnum $function, int $count, int $quality): void
    {
        $queue = $this->moduleQueueRepository->getByColonyAndModuleAndBuilding($colony->getId(), $module->getId(), $function->value, $quality);
        if ($queue === null) {
            $queue = $this->moduleQueueRepository->prototype();
            $queue->setColony($colony);
            $queue->setBuildingFunction($function);
            $queue->setModule($module);
            $queue->setImprovementSteps($quality);
        }
        $queue->setAmount($queue->getAmount() + $count);
        $this->moduleQueueRepository->save($queue);
    }

    /**
     * @param array<int, int> $projectedStorageAmounts
     * @param iterable<ModuleCost> $costs
     */
    private function lowerProjectedStorageAmounts(array &$projectedStorageAmounts, iterable $costs, int $count): void
    {
        foreach ($costs as $cost) {
            $commodityId = $cost->getCommodity()->getId();
            $projectedStorageAmounts[$commodityId] = max(
                0,
                ($projectedStorageAmounts[$commodityId] ?? 0) - $cost->getAmount() * $count
            );
        }
    }

    /**
     * @param iterable<ModuleCost> $costs
     * @param array<int, int> $projectedStorageAmounts
     * @param array<int, array{name: string, missing: int}> $missingCommodityTotals
     *
     * @return array<int, array{name: string, missing: int}>
     */
    private function getMissingCommodities(
        int $missingModuleCount,
        iterable $costs,
        array &$projectedStorageAmounts,
        array &$missingCommodityTotals
    ): array {
        $missingCommodities = [];

        foreach ($costs as $cost) {
            $commodity = $cost->getCommodity();
            $commodityId = $commodity->getId();
            $requiredAmount = $cost->getAmount() * $missingModuleCount;
            $availableAmount = $projectedStorageAmounts[$commodityId] ?? 0;
            $missingAmount = max(0, $requiredAmount - $availableAmount);

            $projectedStorageAmounts[$commodityId] = max(0, $availableAmount - $requiredAmount);

            if ($missingAmount > 0) {
                $missingCommodities[$commodityId] = [
                    'name' => $commodity->getName(),
                    'missing' => $missingAmount
                ];
                $missingCommodityTotals[$commodityId] ??= [
                    'name' => $commodity->getName(),
                    'missing' => 0
                ];
                $missingCommodityTotals[$commodityId]['missing'] += $missingAmount;
            }
        }

        ksort($missingCommodities);

        return $missingCommodities;
    }

    /**
     * @param array<int, array{name: string, missing: int}> $missingCommodities
     *
     * @return array<string>
     */
    private function formatMissingCommodities(array $missingCommodities): array
    {
        return array_map(
            fn (array $commodity): string => sprintf(
                '%d %s',
                $commodity['missing'],
                $commodity['name']
            ),
            $missingCommodities
        );
    }
}
