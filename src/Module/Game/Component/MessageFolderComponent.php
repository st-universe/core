<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Stu\Component\Player\UserAwardEnum;
use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;
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

    #[\Override]
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
                && !$this->hasStationsPmCategory($user)
            ) {
                continue;
            }

            /** @var PrivateMessageFolder $specialFolder */
            $specialFolder = $this->privateMessageFolderRepository->getByUserAndSpecial($user->getId(), $folderType);

            $folder[$folderType->value] = $this->commUiFactory->createPrivateMessageFolderItem($specialFolder);
        }

        $game->setTemplateVar('PM', $folder);
    }

    private function hasStationsPmCategory(User $user): bool
    {
        if ($user->isNpc()) {
            return true;
        }

        return $user->hasAward(UserAwardEnum::RESEARCHED_STATIONS);
    }
}
