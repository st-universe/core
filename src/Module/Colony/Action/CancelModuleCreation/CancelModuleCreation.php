<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CancelModuleCreation;

use Colfields;
use ModuleQueue;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class CancelModuleCreation implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CANCEL_MODULECREATION';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView('SHOW_MODULE_CANCEL');
        $module_id = request::postIntFatal('module');
        $function = request::postIntFatal('func');
        $count = request::postInt('count');
        $module = ResourceCache()->getObject('module', $module_id);
        if (count(Colfields::getFieldsByBuildingFunction($colony->getId(), $function)) == 0) {
            return;
        }
        if ($count == 0) {
            return;
        }
        $queue = ModuleQueue::getBy('WHERE colony_id=' . $colony->getId() . ' AND module_id=' . $module_id . ' AND buildingfunction=' . $function);
        if (!$queue) {
            return;
        }
        if ($queue->getAmount() < $count) {
            $count = $queue->getAmount();
        }
        if ($count >= $queue->getAmount()) {
            $queue->deleteFromDatabase();
        } else {
            $queue->setCount($queue->getAmount() - $count);
            $queue->save();
        }
        if ($module->getEcost() * $count > $colony->getMaxEps() - $colony->getEps()) {
            $colony->setEps($colony->getMaxEps());
        } else {
            $colony->upperEps($count * $module->getEcost());
        }
        foreach ($module->getCost() as $cid => $cost) {
            if ($colony->getStorageSum() >= $colony->getMaxStorage()) {
                break;
            }
            if ($cost->getAmount() * $count > $colony->getMaxStorage() - $colony->getStorageSum()) {
                $gc = $colony->getMaxStorage() - $colony->getStorageSum();
            } else {
                $gc = $count * $cost->getAmount();
            }
            $colony->upperStorage($cost->getGoodId(), $gc);
            $colony->setStorageSum($colony->getStorageSum() + $gc);
        }
        $colony->save();
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
