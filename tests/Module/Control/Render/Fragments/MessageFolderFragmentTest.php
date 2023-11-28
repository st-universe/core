<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\StuTestCase;

class MessageFolderFragmentTest extends StuTestCase
{
    /** @var MockInterface&PrivateMessageFolderRepositoryInterface */
    private MockInterface $privateMessageFolderRepository;

    /** @var MockInterface&PrivateMessageUiFactoryInterface */
    private MockInterface $privateMessageUiFactory;

    private MessageFolderFragment $subject;

    protected function setUp(): void
    {
        $this->privateMessageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->privateMessageUiFactory = $this->mock(PrivateMessageUiFactoryInterface::class);

        $this->subject = new MessageFolderFragment(
            $this->privateMessageFolderRepository,
            $this->privateMessageUiFactory
        );
    }

    public function testRenderRendersFolderListWithoutStation(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);
        $folder = $this->mock(PrivateMessageFolderInterface::class);
        $folderItem = $this->mock(PrivateMessageFolderItem::class);

        $userId = 666;

        $folderTypeIds = [
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        ];

        foreach ($folderTypeIds as $typeId) {
            $this->privateMessageFolderRepository->shouldReceive('getByUserAndSpecial')
                ->with($userId, $typeId)
                ->once()
                ->andReturn($folder);
        }

        $this->privateMessageUiFactory->shouldReceive('createPrivateMessageFolderItem')
            ->with($folder)
            ->times(count($folderTypeIds))
            ->andReturn($folderItem);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('hasStationsNavigation')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $talPage->shouldReceive('setVar')
            ->with(
                'PM_NAVLET',
                [
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM => $folderItem,
                ]
            )
            ->once();

        $this->subject->render($user, $talPage, $this->mock(GameControllerInterface::class));
    }

    public function testRenderRendersFolderListWithStation(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);
        $folder = $this->mock(PrivateMessageFolderInterface::class);
        $folderItem = $this->mock(PrivateMessageFolderItem::class);

        $userId = 666;

        $folderTypeIds = [
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        ];

        foreach ($folderTypeIds as $typeId) {
            $this->privateMessageFolderRepository->shouldReceive('getByUserAndSpecial')
                ->with($userId, $typeId)
                ->once()
                ->andReturn($folder);
        }

        $this->privateMessageUiFactory->shouldReceive('createPrivateMessageFolderItem')
            ->with($folder)
            ->times(count($folderTypeIds))
            ->andReturn($folderItem);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('hasStationsNavigation')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $talPage->shouldReceive('setVar')
            ->with(
                'PM_NAVLET',
                [
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE => $folderItem,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM => $folderItem,
                ]
            )
            ->once();

        $this->subject->render($user, $talPage, $this->mock(GameControllerInterface::class));
    }
}
