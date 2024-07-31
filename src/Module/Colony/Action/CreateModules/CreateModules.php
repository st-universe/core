<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateModules;

use Override;
use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ModuleBuildingFunctionInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class CreateModules implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_MODULES';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository, private ModuleQueueRepositoryInterface $moduleQueueRepository, private PlanetFieldRepositoryInterface $planetFieldRepository, private ColonyStorageManagerInterface $colonyStorageManager, private ColonyRepositoryInterface $colonyRepository) {}

    #[Override]
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

        $modules = request::postArrayFatal('module');
        $func = request::postIntFatal('func');

        if ($this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony,
            [$func],
            [0, 1]
        ) === 0) {
            return;
        }
        $prod = [];

        /** @var ModuleBuildingFunctionInterface[] $modules_av */
        $modules_av = [];
        foreach ($this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser($func, $userId) as $module) {
            $modules_av[$module->getModuleId()] = $module;
        }

        $storage = $colony->getStorage();
        foreach ($modules as $module_id => $count) {
            if (!array_key_exists($module_id, $modules_av)) {
                continue;
            }
            $count = (int)$count;
            if ($count <= 0) {
                continue;
            }
            $initialcount = $count;
            $module = $modules_av[$module_id]->getModule();
            $missingcounteps = 0;
            $missingeps = 0;
            if ($module->getEcost() * $initialcount > $colony->getEps()) {
                $missingeps = $initialcount * $module->getEcost() - $colony->getEps();
                $missingcounteps = $initialcount - (int) floor($colony->getEps() / $module->getEcost());
                $count = (int) floor($colony->getEps() / $module->getEcost());
            }
            if ($count == 0) {
                continue;
            }

            $costs = $module->getCost();

            $isEnoughAvailable = true;
            $missingcount = 0;
            foreach ($costs as $cost) {
                $commodity = $cost->getCommodity();
                $commodityId = $commodity->getId();


                $stor = $storage[$commodityId] ?? null;
                $availableAmount = ($stor !== null) ? $stor->getAmount() : 0;

                if ($availableAmount < $cost->getAmount() * $initialcount) {
                    if ($missingcount < $initialcount - (int) floor($availableAmount / $cost->getAmount())) {
                        $missingcount = $initialcount - (int) floor($availableAmount / $cost->getAmount());
                    }
                    if ($count > $initialcount - $missingcount) {
                        $count = $initialcount - $missingcount;
                    }
                    $isEnoughAvailable = false;
                }
            }
            foreach ($costs as $cost) {
                $this->colonyStorageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount() * $count);
            }
            $colony->lowerEps($count * $module->getEcost());

            $this->colonyRepository->save($colony);
            if ($count > 0) {
                if (($queue = $this->moduleQueueRepository->getByColonyAndModuleAndBuilding($colonyId, (int) $module_id, $func)) !== null) {
                    $queue->setAmount($queue->getAmount() + $count);

                    $this->moduleQueueRepository->save($queue);
                    $moduleAdded = true;
                } else {
                    $queue = $this->moduleQueueRepository->prototype();
                    $queue->setColony($colony);
                    $queue->setBuildingFunction($func);
                    $queue->setModule($module);
                    $queue->setAmount($count);

                    $this->moduleQueueRepository->save($queue);
                    $moduleAdded = true;
                }
            }

            if ($count < $initialcount) {
                $missing = sprintf(_(' von %s'), $initialcount);
            } else {
                $missing = '';
            }

            $prod[] = $count . $missing . ' ' . $module->getName();
            if ($missingeps > 0) {
                $epsmessage = sprintf(
                    _('%s Energie, '),
                    $missingeps
                );
            } else {
                $epsmessage = '';
            }

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
                            'name' => $commodity->getName(),
                            'missing' => $missingAmount
                        ];
                    }
                }

                $missingText = array_map(function ($commodity) {
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
            $game->addInformation(_('Es wurden folgende Module zur Warteschlange hinzugefügt:'));
            foreach ($prod as $msg) {
                $game->addInformation($msg);
            }
        } elseif ($prod !== []) {
            foreach ($prod as $msg) {
                $game->addInformation($msg);
            }
        } else {
            $game->addInformation(_('Es wurden keine Module hergestellt oder ausgewählt'));
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
