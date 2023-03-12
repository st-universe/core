<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

/**
 * Renders the pm folders in the header
 */
final class MessageFolderFragment implements RenderFragmentInterface
{
    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageUiFactoryInterface $commUiFactory;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageUiFactoryInterface $privateMessageUiFactory
    ){
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->commUiFactory = $privateMessageUiFactory;
    }

    public function render(
        UserInterface $user,
        TalPageInterface $talPage
    ): void {
        $userId = $user->getId();

        $pmFolder = [
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
        ];
        $folder = [];

        foreach ($pmFolder as $specialId) {
            if (
                $specialId === PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
                && !$user->hasStationsNavigation()
            ) {
                continue;
            }

            /** @var PrivateMessageFolderInterface $specialFolder */
            $specialFolder = $this->privateMessageFolderRepository->getByUserAndSpecial($userId, $specialId);

            $folder[$specialId] = $this->commUiFactory->createPrivateMessageFolderItem($specialFolder);
        }

        $talPage->setVar('PM_NAVLET', $folder);
    }
}
