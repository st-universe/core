<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;

final class SpecialResearchCompletion
{
    public function __construct(
        private SpecialResearchRepositoryInterface $repository,
        private PrivateMessageSenderInterface $messages,
        private StuTime $time
    ) {}

    public function finish(SpecialResearch $research, bool $failed, string $details = ''): void
    {
        if ($research->getStatus() !== SpecialResearchStatusEnum::RUNNING) {
            return;
        }
        $research->setStatus($failed ? SpecialResearchStatusEnum::FAILED : SpecialResearchStatusEnum::COMPLETED);
        $this->repository->save($research);
        $parts = [sprintf('Die Grundlagenforschung für %s ist %s', $research->getResearchType()->getDescription(), $failed ? 'fehlgeschlagen' : 'erfolgreich abgeschlossen')];
        if ($details !== '') {
            $parts[] = $details;
        }
        if ($failed) {
            $parts[] = 'Ein neuer Versuch ist mit neuen Materialien möglich';
        }
        $this->messages->send(UserConstants::USER_NOONE, $research->getUser()->getId(), implode('. ', $parts), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
    }

    public function facilityLost(SpecialResearch $research, int $timestamp): void
    {
        if ($research->getStatus() !== SpecialResearchStatusEnum::RUNNING) {
            return;
        }
        $research->setFinishTime(min($research->getFinishTime(), $timestamp));
        $this->finish($research, true, 'Das Forschungsgebäude wurde entfernt, zerstört oder steht nicht mehr zur Verfügung');
    }

    public function buildingRemoved(PlanetField $field): void
    {
        foreach ($this->repository->getRunningByField($field) as $research) {
            $this->facilityLost($research, $this->time->time());
        }
    }
}
