<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Mockery\MockInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\StuTestCase;

class MessageFolderFragmentTest extends StuTestCase
{
    /** @var MockInterface&PrivateMessageFolderRepositoryInterface */
    private MockInterface $privateMessageFolderRepository;

    private MessageFolderFragment $subject;

    protected function setUp(): void
    {
        $this->privateMessageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);

        $this->subject = new MessageFolderFragment(
            $this->privateMessageFolderRepository
        );
    }

    public function testRenderRendersFolderListWithoutStation(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);
        $folder = $this->mock(PrivateMessageFolderInterface::class);

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
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM => $folder,
                ]
            )
            ->once();

        $this->subject->render($user, $talPage);
    }

    public function testRenderRendersFolderListWithStation(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);
        $folder = $this->mock(PrivateMessageFolderInterface::class);

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
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE => $folder,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM => $folder,
                ]
            )
            ->once();

        $this->subject->render($user, $talPage);
    }
}
