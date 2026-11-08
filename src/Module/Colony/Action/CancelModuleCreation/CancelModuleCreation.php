<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelModuleCreation;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Research\ModuleProductionQuality;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ModuleQueue;
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
        private ColonyRepositoryInterface $colonyRepository,
        private ModuleProductionQuality $productionQuality
    ) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(request::indInt('id'), $context->getUser()->getId());
        $moduleId = request::postIntFatal('module');
        $function = request::postIntFatal('func');
        $count = request::postInt('count');
        $module = $this->moduleRepository->find($moduleId);
        if ($module === null) {
            return;
        }
        $context->setView('SHOW_MODULE_CANCEL');
        $context->setViewContext(ViewContextMetadataTypeEnum::MODULE, $module);
        if ($count < 1 || $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony,
            [BuildingFunctionEnum::from($function)],
            [0, 1]
        ) === 0) {
            return;
        }
        $queues = $this->getQueues($colony, $moduleId, $function);
        if ($queues === []) {
            return;
        }
        foreach ($queues as $queue) {
            if ($count === 0) {
                break;
            }
            $cancelled = min($count, $queue->getAmount());
            $this->cancelQueue($queue, $cancelled);
            $this->refundEnergy($colony, $queue, $cancelled);
            $this->refundMaterials($colony, $queue, $cancelled);
            $count -= $cancelled;
        }
        $this->colonyRepository->save($colony);
    }

    /** @return list<ModuleQueue> */
    private function getQueues(Colony $colony, int $moduleId, int $function): array
    {
        $queues = array_values(array_filter(
            $this->moduleQueueRepository->getByColonyAndBuilding($colony->getId(), [$function]),
            static fn (ModuleQueue $queue): bool => $queue->getModule()->getId() === $moduleId
        ));
        usort($queues, static fn (ModuleQueue $first, ModuleQueue $second): int => $first->getImprovementSteps() <=> $second->getImprovementSteps());
        return $queues;
    }

    private function cancelQueue(ModuleQueue $queue, int $count): void
    {
        if ($count >= $queue->getAmount()) {
            $this->moduleQueueRepository->delete($queue);
        } else {
            $queue->setAmount($queue->getAmount() - $count);
            $this->moduleQueueRepository->save($queue);
        }
    }

    private function refundEnergy(Colony $colony, ModuleQueue $queue, int $count): void
    {
        $changeable = $colony->getChangeable();
        $changeable->setEps(min($colony->getMaxEps(), $changeable->getEps() + $count * $queue->getModule()->getEcost()));
    }

    private function refundMaterials(Colony $colony, ModuleQueue $queue, int $count): void
    {
        foreach ($this->productionQuality->getCosts($queue->getModule(), $queue->getImprovementSteps()) as $cost) {
            $available = $colony->getMaxStorage() - $colony->getStorageSum();
            if ($available <= 0) {
                break;
            }
            $this->storageManager->upperStorage($colony, $cost->getCommodity(), min($available, $count * $cost->getAmount()));
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
