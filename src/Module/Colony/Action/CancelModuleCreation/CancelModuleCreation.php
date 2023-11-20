<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelModuleCreation;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class CancelModuleCreation implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_MODULECREATION';

    private ColonyLoaderInterface $colonyLoader;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    private ModuleRepositoryInterface $moduleRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ModuleQueueRepositoryInterface $moduleQueueRepository,
        ModuleRepositoryInterface $moduleRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->moduleQueueRepository = $moduleQueueRepository;
        $this->moduleRepository = $moduleRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $module_id = request::postIntFatal('module');
        $function = request::postIntFatal('func');
        $count = request::postInt('count');

        /** @var ModuleInterface $module */
        $module = $this->moduleRepository->find($module_id);

        if ($module === null) {
            return;
        }

        $game->setView('SHOW_MODULE_CANCEL', ['MODULE' => $module]);

        if ($this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $colony,
            [$function],
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
        if ($module->getEcost() * $count > $colony->getMaxEps() - $colony->getEps()) {
            $colony->setEps($colony->getMaxEps());
        } else {
            $colony->upperEps($count * $module->getEcost());
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

            $this->colonyStorageManager->upperStorage($colony, $cost->getCommodity(), $gc);
        }
        $this->colonyRepository->save($colony);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
