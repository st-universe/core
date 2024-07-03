<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishTerraformingJobs implements ProcessTickHandlerInterface
{
    public function __construct(private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository, private PlanetFieldRepositoryInterface $planetFieldRepository, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function work(): void
    {
        $result = $this->colonyTerraformingRepository->getFinishedJobs();
        foreach ($result as $colonyTerraforming) {
            $colonyField = $colonyTerraforming->getField();

            $host = $colonyField->getHost();
            if ($host instanceof ColonySandboxInterface) {
                continue;
            }

            $terraforming = $colonyTerraforming->getTerraforming();
            $colonyField->setFieldType($terraforming->getToFieldTypeId());
            $colonyField->setTerraforming(null);

            $this->planetFieldRepository->save($colonyField);

            $this->colonyTerraformingRepository->delete($colonyTerraforming);
            $txt = "Kolonie " . $host->getName() . ": " . $terraforming->getDescription() . " auf Feld " . $colonyField->getFieldId() . " abgeschlossen";

            $href = sprintf('colony.php?%s=1&id=%d', ShowColony::VIEW_IDENTIFIER, $host->getId());

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $host->getUserId(),
                $txt,
                PrivateMessageFolderTypeEnum::SPECIAL_COLONY,
                $href
            );
        }
    }
}
