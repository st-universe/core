<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Override;
use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

/**
 * Renders the pm folders in the header
 */
final class MessageFolderComponent implements ComponentInterface
{
    public function __construct(
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private PrivateMessageUiFactoryInterface $commUiFactory
    ) {}

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $pmFolder = [
            PrivateMessageFolderTypeEnum::SPECIAL_MAIN,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY,
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
        ];
        $folder = [];

        foreach ($pmFolder as $folderType) {
            if (
                $folderType === PrivateMessageFolderTypeEnum::SPECIAL_STATION
                && !$user->hasStationsNavigation()
            ) {
                continue;
            }

            /** @var PrivateMessageFolderInterface $specialFolder */
            $specialFolder = $this->privateMessageFolderRepository->getByUserAndSpecial($user->getId(), $folderType);

            $folder[$folderType->value] = $this->commUiFactory->createPrivateMessageFolderItem($specialFolder);
        }

        $game->setTemplateVar('PM', $folder);
    }
}
