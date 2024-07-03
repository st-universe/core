<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

/**
 * Renders the pm folders in the header
 */
final class MessageFolderFragment implements RenderFragmentInterface
{
    public function __construct(private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository, private PrivateMessageUiFactoryInterface $commUiFactory)
    {
    }

    #[Override]
    public function render(
        UserInterface $user,
        TalPageInterface|TwigPageInterface $page,
        GameControllerInterface $game
    ): void {
        $userId = $user->getId();

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
            $specialFolder = $this->privateMessageFolderRepository->getByUserAndSpecial($userId, $folderType);

            $folder[$folderType->value] = $this->commUiFactory->createPrivateMessageFolderItem($specialFolder);
        }

        $page->setVar('PM_NAVLET', $folder);
    }
}
