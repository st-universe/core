<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks\Colony;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Config\Model\ColonySettings;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ManualColonyTick implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COLONY_TICK';

    public function __construct(private ManualColonyTickRequestInterface $request, private ColonyTickManagerInterface $colonyTickManager, private ColonyTickInterface $colonyTick, private ColonyRepositoryInterface $colonyRepository, private StuConfigInterface $config) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowTicks::VIEW_IDENTIFIER);
        $colonyId = $this->request->getColonyId();

        //check if single or multiple colonies
        if ($colonyId === null) {
            $this->executeTickForMultipleColonies($context);
        } else {
            $this->executeTickForSingleColony($colonyId, $context);
        }
    }

    private function executeTickForMultipleColonies(ActionControllerContext $context): void
    {
        $groupId = $this->request->getGroupId();

        if ($groupId !== null) {
            $groupCount = $this->getGroupCount();
            $this->colonyTickManager->work($groupId, $groupCount);

            $context->getInfo()->addInformationf("Der Kolonie-Tick für die Kolonie-Gruppe %d/%d wurde durchgeführt!", $groupId, $groupCount);
        } else {
            $this->colonyTickManager->work(1, ColonySettings::SETTING_TICK_WORKER_DEFAULT);
            $context->getInfo()->addInformation("Der Kolonie-Tick für alle Kolonien wurde durchgeführt!");
        }
    }

    private function getGroupCount(): int
    {
        return $this->config->getGameSettings()->getColonySettings()->getTickWorker();
    }

    private function executeTickForSingleColony(int $colonyId, ActionControllerContext $context): void
    {
        $colony = $this->colonyRepository->find($colonyId);

        if ($colony === null) {
            $context->getInfo()->addInformationf("Keine Kolonie mit der ID %d vorhanden!", $colonyId);
            return;
        }

        $this->colonyTick->work($colony);

        $context->getInfo()->addInformationf("Der Kolonie-Tick für die Kolonie mit der ID %d wurde durchgeführt!", $colonyId);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
