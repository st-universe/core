<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishBuildJobs implements ProcessTickHandlerInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        BuildingManagerInterface $buildingManager
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->buildingManager = $buildingManager;
    }

    public function work(): void
    {
        $result = $this->planetFieldRepository->getByConstructionFinish(time());
        foreach ($result as $field) {
            $this->buildingManager->finish($field, $field->getActivateAfterBuild());
            $host = $field->getHost();
            if ($host instanceof ColonySandboxInterface) {
                continue;
            }

            $txt = sprintf(
                "Kolonie %s: %s auf Feld %s fertiggestellt",
                $host->getName(),
                $field->getBuilding()->getName(),
                $field->getFieldId()
            );

            $href = sprintf('colony.php?%s=1&id=%d', ShowColony::VIEW_IDENTIFIER, $host->getId());

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $host->getUserId(),
                $txt,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
                $href
            );
        }
    }
}
