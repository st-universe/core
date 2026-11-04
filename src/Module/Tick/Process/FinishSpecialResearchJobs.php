<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;

final class FinishSpecialResearchJobs implements ProcessTickHandlerInterface
{
    public function __construct(
        private SpecialResearchRepositoryInterface $repository,
        private StuRandom $random,
        private StuTime $time,
        private PrivateMessageSenderInterface $messages
    ) {}

    #[\Override]
    public function work(): void
    {
        foreach ($this->repository->getDue($this->time->time()) as $research) {
            $failed = $this->random->rand(1, 100) <= $research->getFailureChance();
            $research->setStatus($failed ? SpecialResearchStatusEnum::FAILED : SpecialResearchStatusEnum::COMPLETED);
            $this->repository->save($research);
            $this->messages->send(
                UserConstants::USER_NOONE,
                $research->getUser()->getId(),
                sprintf(
                    'Die Grundlagenforschung für %s ist %s',
                    $research->getResearchType()->getDescription(),
                    $failed ? 'fehlgeschlagen. Ein neuer Versuch ist mit neuen Materialien möglich' : 'erfolgreich abgeschlossen'
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_COLONY
            );
        }
    }
}
