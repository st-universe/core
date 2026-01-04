<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateModules;

use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ModuleBuildingFunction;
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
        private ColonyRepositoryInterface $colonyRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $moduleAdded = false;

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $colonyId = $colony->getId();

        $moduleIds = request::getArrayFatal('moduleids');
        $values = request::getArrayFatal('values');
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
            $missingcounteps = 0;
            $missingeps = 0;
            if ($module->getEcost() * $initialcount > $changeable->getEps()) {
                $missingeps = $initialcount * $module->getEcost() - $changeable->getEps();
                $missingcounteps = $initialcount - (int) floor($changeable->getEps() / $module->getEcost());
                $count = (int) floor($changeable->getEps() / $module->getEcost());
            }
            $costs = $module->getCost();

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
                $changeable->lowerEps($count * $module->getEcost());

                $this->colonyRepository->save($colony);
                if (($queue = $this->moduleQueueRepository->getByColonyAndModuleAndBuilding($colonyId, (int) $moduleId, $function->value)) !== null) {
                    $queue->setAmount($queue->getAmount() + $count);

                    $this->moduleQueueRepository->save($queue);
                    $moduleAdded = true;
                } else {
                    $queue = $this->moduleQueueRepository->prototype();
                    $queue->setColony($colony);
                    $queue->setBuildingFunction($function);
                    $queue->setModule($module);
                    $queue->setAmount($count);

                    $this->moduleQueueRepository->save($queue);
                    $moduleAdded = true;
                }
            }
            if ($count == $initialcount) {
                $isEnoughAvailable = true;
            }

            $missing = $isEnoughAvailable ? '' : sprintf(_(' von %s'), $initialcount);

            $prod[] = $count . $missing . ' ' . $module->getName();
            $epsmessage = $missingeps > 0 ? sprintf(
                _('%s Energie, '),
                $missingeps
            ) : '';

            if (!$isEnoughAvailable) {
                $missingCommodities = [];

                foreach ($costs as $cost) {
                    $commodity = $cost->getCommodity();
                    $commodityId = $commodity->getId();
                    $stor = $storage[$commodityId] ?? null;
                    $availableAmount = ($stor !== null) ? $stor->getAmount() : 0;

                    $requiredAmount = $cost->getAmount() * $missingcount;
                    $missingAmount = $requiredAmount - $availableAmount;

                    if ($missingAmount > 0) {
                        $missingCommodities[] = [
                            'id' => $commodityId,
                            'name' => $commodity->getName(),
                            'missing' => $missingAmount
                        ];
                    }
                }

                usort($missingCommodities, function (array $a, array $b): int {
                    return $a['id'] <=> $b['id'];
                });

                $missingText = array_map(function (array $commodity): string {
                    return sprintf(
                        '%d %s',
                        $commodity['missing'],
                        $commodity['name']
                    );
                }, $missingCommodities);

                $prod[] = sprintf(
                    _('<div style="padding-left: 20px;">- Zur Herstellung von weiteren %d %s werden benötigt: %s%s</div>'),
                    max($missingcount, $missingcounteps),
                    $module->getName(),
                    $epsmessage,
                    implode(', ', $missingText)
                );
            }
        }

        if ($moduleAdded) {
            $game->getInfo()->addInformation(_('Es wurden folgende Module zur Warteschlange hinzugefügt:'));
            foreach ($prod as $msg) {
                $game->getInfo()->addInformation($msg);
            }
        } elseif ($prod !== []) {
            foreach ($prod as $msg) {
                $game->getInfo()->addInformation($msg);
            }
        } else {
            $game->getInfo()->addInformation(_('Es wurden keine Module hergestellt oder ausgewählt'));
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
