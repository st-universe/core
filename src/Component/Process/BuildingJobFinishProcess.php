<?php

declare(strict_types=1);

namespace Stu\Component\Process;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingJobFinishProcess implements BuildingJobFinishProcessInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ColonyRepositoryInterface $colonyRepository;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ColonyRepositoryInterface $colonyRepository,
        BuildingManagerInterface $buildingManager
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->colonyRepository = $colonyRepository;
        $this->buildingManager = $buildingManager;
    }

    public function work(BuildingJobProcessMessageInterface $message): void
    {
        $field = $this->planetFieldRepository->find($message->getPlanetFieldId());

        if ($field->isInConstruction() === false) {
            return;
        }

        $this->buildingManager->finish($field);
        $colony = $field->getColony();

        $txt = sprintf(
            'Kolonie %s: %s auf Feld %s fertiggestellt',
            $colony->getName(),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int)$colony->getUserId(),
            $txt,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
        );
    }
}
