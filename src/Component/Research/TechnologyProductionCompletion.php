<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\TechnologyProduction;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class TechnologyProductionCompletion
{
    public function __construct(
        private TechnologyRepositoryInterface $repository,
        private TechnologyFacility $facility,
        private TechnologyBuildingLifecycle $lifecycle,
        private StorageManagerInterface $storage,
        private PrivateMessageSenderInterface $messages,
        private StuTime $time
    ) {}

    public function work(bool $productionTick): void
    {
        foreach ($this->repository->getRunningProduction($productionTick) as $job) {
            $this->finish($job);
        }
    }

    private function finish(TechnologyProduction $job): void
    {
        $field = $this->facility->getField($job);
        if ($field === null) {
            $this->lifecycle->abort($job);
            return;
        }
        $colony = $job->getColony();
        if ($colony === null || !$field->isActive() || $job->getFinishTime() > $this->time->time()) {
            return;
        }
        if ($colony->getMaxStorage() - $colony->getStorageSum() < $job->getOutputAmount()) {
            return;
        }
        $this->storage->upperStorage($colony, $job->getOutputCommodity(), $job->getOutputAmount());
        $job->setStatus(SpecialResearchStatusEnum::COMPLETED);
        $this->repository->save($job);
        $this->messages->send(UserConstants::USER_NOONE, $job->getUser()->getId(), sprintf('Auf %s wurden %d %s hergestellt', $colony->getName(), $job->getOutputAmount(), $job->getOutputCommodity()->getName()), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
    }
}
