<?php

declare(strict_types=1);

namespace Stu\Component\Process;

use Stu\Component\Game\GameEnum;
use Stu\Component\Queue\Message\Type\TerraformingJobProcessMessageInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class TerraformingJobFinishProcess implements TerraformingJobFinishProcessInterface
{
    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
    }

    public function work(TerraformingJobProcessMessageInterface $message): void
    {
        $terraforming_job = $this->colonyTerraformingRepository->find($message->getTerraformingId());

        $field = $terraforming_job->getField();
        $colony = $terraforming_job->getColony();

        $field->setFieldType($field->getTerraforming()->getToFieldTypeId());
        $field->setTerraforming(null);

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int)$colony->getUserId(),
            $txt = sprintf(
                'Kolonie %s: %s auf Feld %s abgeschlossen',
                $colony->getName(),
                $terraforming_job->getTerraforming()->getDescription(),
                $field->getFieldId()
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
        );

        $this->planetFieldRepository->save($field);

        $this->colonyTerraformingRepository->delete($terraforming_job);
    }
}
