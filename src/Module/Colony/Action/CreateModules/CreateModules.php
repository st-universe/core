<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateModules;

use Colfields;
use Exception;
use ModuleBuildingFunction;
use ModuleQueue;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class CreateModules implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CREATE_MODULES';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $modules = request::postArrayFatal('module');
        $func = request::postIntFatal('func');
        if (count(Colfields::getFieldsByBuildingFunction($colony->getId(), $func)) == 0) {
            return;
        }
        $prod = array();
        $modules_av = ModuleBuildingFunction::getByFunctionAndUser($func, currentUser()->getId());
        $storage = $colony->getStorage();
        foreach ($modules as $module_id => $count) {
            if (!array_key_exists($module_id, $modules_av)) {
                continue;
            }
            $count = intval($count);
            $module = $modules_av[$module_id]->getModule();
            if ($module->getEcost() * $count > $colony->getEps()) {
                $count = floor($colony->getEps() / $module->getEcost());
            }
            if ($count == 0) {
                continue;
            }
            try {
                foreach ($module->getCost() as $cid => $cost) {
                    if (!array_key_exists($cost->getGoodId(), $storage)) {
                        $prod[] = sprintf(
                            _('Zur Herstellung von %s wird %s benötigt'),
                            $module->getName(),
                            $cost->getGood()->getName()
                        );
                        throw new Exception;
                    }
                    if ($storage[$cost->getGoodId()]->getAmount() < $cost->getAmount()) {
                        $prod[] = sprintf(
                            _('Zur Herstellung von %s wird %d %s benötigt'),
                            $module->getName(),
                            $cost->getAmount(),
                            $cost->getGood()->getName()
                        );
                        throw new Exception;
                    }
                    if ($storage[$cost->getGoodId()]->getAmount() < $cost->getAmount() * $count) {
                        $count = floor($storage[$cost->getGoodId()]->getAmount() / $cost->getAmount());
                    }
                }
            } catch (Exception $e) {
                continue;
            }
            foreach ($module->getCost() as $cid => $cost) {
                $colony->lowerStorage($cost->getGoodId(), $cost->getAmount() * $count);
            }
            $colony->lowerEps($count * $module->getEcost());
            $colony->save();
            ModuleQueue::queueModule($colony->getId(), $func, $module_id, $count);
            $prod[] = $count . ' ' . $module->getName();
        }
        if (count($prod) == 0) {
            $game->addInformation(_('Es wurden keine Module hergestellt'));
            return;
        }
        $game->addInformation(_('Es wurden folgende Module zur Warteschlange hinzugefügt'));
        foreach ($prod as $msg) {
            $game->addInformation($msg);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
