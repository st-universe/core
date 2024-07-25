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

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository,
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private ColonyStorageManagerInterface $colonyStorageManager,
        private ColonyRepositoryInterface $colonyRepository
    ) {}

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
        $missingResources = [];

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
            $module = $modules_av[$module_id]->getModule();
            $initialCount = $count;


            if ($module->getEcost() * $count > $colony->getEps()) {
                $count = (int) floor($colony->getEps() / $module->getEcost());
            }
            if ($count == 0) {
                $missingResources[] = sprintf(
                    _('Zur Herstellung von %s fehlt Energie.'),
                    $module->getName()
                );
                continue;
            }

            $costs = $module->getCost();

            $isEnoughAvailable = true;
            $missingForModule = [];
            foreach ($costs as $cost) {
                $commodity = $cost->getCommodity();
                $commodityId = $commodity->getId();

                $stor = $storage[$commodityId] ?? null;
                if ($stor === null || $stor->getAmount() < $cost->getAmount()) {
                    $missingForModule[] = sprintf(
                        '%d %s',
                        $cost->getAmount(),
                        $commodity->getName()
                    );
                    $isEnoughAvailable = false;
                    continue;
                }
                if ($stor->getAmount() < $cost->getAmount() * $count) {
                    $count = (int) floor($stor->getAmount() / $cost->getAmount());
                }
            }

            if (!$isEnoughAvailable) {
                $missingResources[] = sprintf(
                    _('Zur Herstellung von %s fehlen: %s'),
                    $module->getName(),
                    implode(', ', $missingForModule)
                );
                continue;
            }
            foreach ($costs as $cost) {
                $this->colonyStorageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount() * $count);
            }
            $colony->lowerEps($count * $module->getEcost());

            $this->colonyRepository->save($colony);

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

            $prod[] = $count . ' ' . $module->getName();

            if ($initialCount > $count) {
                $missingResources[] = sprintf(
                    _('Für die Herstellung von %d weiteren %s fehlen Ressourcen: %s'),
                    $initialCount - $count,
                    $module->getName(),
                    implode(', ', $missingForModule)
                );
            }
        }
        if ($moduleAdded) {
            $game->addInformation(_('Es wurden folgende Module zur Warteschlange hinzugefügt'));
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

        if (!empty($missingResources)) {
            $game->addInformation(_('Es konnten nicht alle gewünschten Module hergestellt werden:'));
            foreach ($missingResources as $msg) {
                $game->addInformation($msg);
            }
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
