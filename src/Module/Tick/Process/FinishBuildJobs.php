<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishBuildJobs implements ProcessTickHandlerInterface
{
    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository, private PrivateMessageSenderInterface $privateMessageSender, private BuildingManagerInterface $buildingManager) {}

    #[Override]
    public function work(): void
    {
        $result = $this->planetFieldRepository->getByConstructionFinish(time());
        foreach ($result as $field) {
            $activationDetails = $this->buildingManager->finish($field, $field->getActivateAfterBuild());
            $host = $field->getHost();
            if ($host instanceof ColonySandbox) {
                continue;
            }

            $txt = sprintf(
                "Kolonie %s: %s auf Feld %s fertiggestellt\n%s",
                $host->getName(),
                $field->getBuilding()->getName(),
                $field->getFieldId(),
                $activationDetails ?? ''
            );

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $host->getUserId(),
                $txt,
                PrivateMessageFolderTypeEnum::SPECIAL_COLONY,
                $host
            );
        }
    }
}
