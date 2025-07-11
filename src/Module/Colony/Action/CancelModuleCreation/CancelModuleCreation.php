<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelModuleCreation;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class CancelModuleCreation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_MODULECREATION';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $module_id = request::postIntFatal('module');
        $function = request::postIntFatal('func');
        $count = request::postInt('count');

        $module = $this->moduleRepository->find($module_id);
        if ($module === null) {
            return;
        }

        $game->setView('SHOW_MODULE_CANCEL');
        $game->setViewContext(ViewContextTypeEnum::MODULE, $module);

        if ($this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony,
            [BuildingFunctionEnum::from($function)],
            [0, 1]
        ) === 0) {
            return;
        }
        if ($count == 0) {
            return;
        }
        $queue = $this->moduleQueueRepository->getByColonyAndModuleAndBuilding($colony->getId(), $module_id, $function);
        if ($queue === null) {
            return;
        }
        if ($queue->getAmount() < $count) {
            $count = $queue->getAmount();
        }
        if ($count >= $queue->getAmount()) {
            $this->moduleQueueRepository->delete($queue);
        } else {
            $queue->setAmount($queue->getAmount() - $count);

            $this->moduleQueueRepository->save($queue);
        }

        $changeable = $colony->getChangeable();

        if ($module->getEcost() * $count > $colony->getMaxEps() - $changeable->getEps()) {
            $changeable->setEps($colony->getMaxEps());
        } else {
            $changeable->upperEps($count * $module->getEcost());
        }
        foreach ($module->getCost() as $cost) {
            if ($colony->getStorageSum() >= $colony->getMaxStorage()) {
                break;
            }
            if ($cost->getAmount() * $count > $colony->getMaxStorage() - $colony->getStorageSum()) {
                $gc = $colony->getMaxStorage() - $colony->getStorageSum();
            } else {
                $gc = $count * $cost->getAmount();
            }

            $this->storageManager->upperStorage($colony, $cost->getCommodity(), $gc);
        }
        $this->colonyRepository->save($colony);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
