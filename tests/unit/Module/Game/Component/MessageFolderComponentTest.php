<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Mockery\MockInterface;
use Stu\Component\Player\UserAwardEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\StuTestCase;

class MessageFolderComponentTest extends StuTestCase
{
    private MockInterface&PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private MockInterface&PrivateMessageUiFactoryInterface $privateMessageUiFactory;

    private MessageFolderComponent $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->privateMessageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->privateMessageUiFactory = $this->mock(PrivateMessageUiFactoryInterface::class);

        $this->subject = new MessageFolderComponent(
            $this->privateMessageFolderRepository,
            $this->privateMessageUiFactory
        );
    }

    public function testRenderRendersFolderListWithoutStation(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $folder = $this->mock(PrivateMessageFolder::class);
        $folderItem = $this->mock(PrivateMessageFolderItem::class);

        $userId = 666;

        $folderTypeIds = [
            PrivateMessageFolderTypeEnum::SPECIAL_MAIN,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY,
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
        ];

        foreach ($folderTypeIds as $typeId) {
            $this->privateMessageFolderRepository->shouldReceive('getByUserAndSpecial')
                ->with($userId, $typeId)
                ->once()
                ->andReturn($folder);
        }

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->privateMessageUiFactory->shouldReceive('createPrivateMessageFolderItem')
            ->with($folder)
            ->times(count($folderTypeIds))
            ->andReturn($folderItem);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($userId);
        $user->shouldReceive('isNpc')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('hasAward')
            ->with(UserAwardEnum::RESEARCHED_STATIONS)
            ->once()
            ->andReturnFalse();

        $game->shouldReceive('setTemplateVar')
            ->with(
                'PM',
                [
                    PrivateMessageFolderTypeEnum::SPECIAL_MAIN->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_COLONY->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_TRADE->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM->value => $folderItem,
                ]
            )
            ->once();

        $this->subject->setTemplateVariables($game);
    }

    public function testRenderRendersFolderListWithStation(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $folder = $this->mock(PrivateMessageFolder::class);
        $folderItem = $this->mock(PrivateMessageFolderItem::class);

        $userId = 666;

        $folderTypeIds = [
            PrivateMessageFolderTypeEnum::SPECIAL_MAIN,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY,
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
        ];

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);


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
            ->andReturn($userId);
        $user->shouldReceive('isNpc')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $game->shouldReceive('setTemplateVar')
            ->with(
                'PM',
                [
                    PrivateMessageFolderTypeEnum::SPECIAL_MAIN->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_STATION->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_COLONY->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_TRADE->value => $folderItem,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM->value => $folderItem,
                ]
            )
            ->once();

        $this->subject->setTemplateVariables($game);
    }
}
