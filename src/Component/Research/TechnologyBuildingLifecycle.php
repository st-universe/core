<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\TechnologyProduction;
use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class TechnologyBuildingLifecycle
{
    public function __construct(private TechnologyRepositoryInterface $repository, private PrivateMessageSenderInterface $messages, private StuTime $time) {}

    public function buildingRemoved(PlanetField $field): void
    {
        foreach ($this->repository->getRunningByField($field) as $job) {
            $this->abort($job);
        }
    }

    public function abort(TechnologyResearch|TechnologyProduction $job): void
    {
        if ($job->getStatus() !== SpecialResearchStatusEnum::RUNNING) {
            return;
        }
        $job->setStatus(SpecialResearchStatusEnum::FAILED)->setFinishTime(min($job->getFinishTime(), $this->time->time()));
        $this->repository->save($job);
        $this->messages->send(UserConstants::USER_NOONE, $job->getUser()->getId(), sprintf('%s ist fehlgeschlagen. Das zugehörige Gebäude wurde entfernt, zerstört oder steht nicht mehr zur Verfügung', $job->getRecipe()->getName()), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
    }
}
