<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishTerraformingJobs implements ProcessTickHandlerInterface
{
    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function work(): void
    {
        $result = $this->colonyTerraformingRepository->getFinishedJobs();
        foreach ($result as $field) {
            $colonyField = $field->getField();
            $colony = $field->getColony();

            $colonyField->setFieldType($field->getTerraforming()->getToFieldTypeId());
            $colonyField->setTerraforming(null);

            $this->planetFieldRepository->save($colonyField);

            $this->colonyTerraformingRepository->delete($field);
            $txt = "Kolonie " . $colony->getName() . ": " . $field->getTerraforming()->getDescription() . " auf Feld " . $colonyField->getFieldId() . " abgeschlossen";

            $href = sprintf('colony.php?%s=1&id=%d', ShowColony::VIEW_IDENTIFIER, $colony->getId());

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $colony->getUserId(),
                $txt,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
                $href
            );
        }
    }
}
