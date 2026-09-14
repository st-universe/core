<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider\Message;

use Mockery;
use Mockery\MockInterface;
use request;
use RuntimeException;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\StuTestCase;

class MessengerStyleProviderTest extends StuTestCase
{
    private MockInterface&PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;
    private MockInterface&PrivateMessageRepositoryInterface $privateMessageRepository;
    private MockInterface&ContactRepositoryInterface $contactRepository;
    private MockInterface&UserSettingsProviderInterface $userSettingsProvider;

    private StuTime $stuTime;

    private MessengerStyleProvider $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->privateMessageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->privateMessageRepository = $this->mock(PrivateMessageRepositoryInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);
        $this->userSettingsProvider = $this->mock(UserSettingsProviderInterface::class);
        $this->stuTime = new StuTime();

        $this->subject = new MessengerStyleProvider(
            $this->privateMessageFolderRepository,
            $this->privateMessageRepository,
            $this->contactRepository,
            $this->userSettingsProvider,
            $this->stuTime
        );
    }

    public function testSetTemplateVariablesThrowsWhenMainFolderIsMissing(): void
    {
        $user = $this->mock(User::class);
        $game = $this->mock(GameControllerInterface::class);
        $userId = 42;

        $game->shouldReceive('getUser')->withNoArgs()->once()->andReturn($user);
        $user->shouldReceive('getId')->withNoArgs()->once()->andReturn($userId);
        $this->privateMessageFolderRepository
            ->shouldReceive('getByUserAndSpecial')
            ->with($userId, PrivateMessageFolderTypeEnum::SPECIAL_MAIN)
            ->once()
            ->andReturnNull();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('main PM category not found');

        $this->subject->setTemplateVariables($game);
    }

    public function testSetTemplateVariablesGroupsConversationsAndResetsInvalidMark(): void
    {
        $user = $this->mock(User::class);
        $game = $this->mock(GameControllerInterface::class);
        $message = $this->mock(PrivateMessage::class);
        $sender = $this->mock(User::class);
        $mainFolder = $this->mock(PrivateMessageFolder::class);

        $userId = 42;
        $senderId = 99;
        $messageTimestamp = 1700000000;

        request::setMockVars(['mark' => 7]);

        $game->shouldReceive('getUser')->withNoArgs()->once()->andReturn($user);
        $user->shouldReceive('getId')->withNoArgs()->once()->andReturn($userId);

        $this->privateMessageFolderRepository
            ->shouldReceive('getByUserAndSpecial')
            ->with($userId, PrivateMessageFolderTypeEnum::SPECIAL_MAIN)
            ->once()
            ->andReturn($mainFolder);

        $this->privateMessageRepository
            ->shouldReceive('getConversations')
            ->with($user, 0, 20)
            ->once()
            ->andReturn([
                'items' => [$message],
                'total' => 1,
            ]);

        $message->shouldReceive('getSender')->withNoArgs()->atLeast()->once()->andReturn($sender);
        $message->shouldReceive('getFormerSendUser')->withNoArgs()->once()->andReturn(null);
        $message->shouldReceive('getCategory')->withNoArgs()->once()->andReturn($mainFolder);
        $message->shouldReceive('getDate')->withNoArgs()->once()->andReturn($messageTimestamp);
        $sender->shouldReceive('getId')->withNoArgs()->once()->andReturn($senderId);

        $this->privateMessageRepository
            ->shouldReceive('getNewAmountByFolderAndSender')
            ->with($mainFolder, $sender)
            ->once()
            ->andReturn(3);

        $game->shouldReceive('setTemplateVar')->with('CONVERSATIONS', Mockery::on(function (array $value) use ($senderId): bool {
            return count($value) === 1
                && array_key_exists($senderId, $value)
                && $value[$senderId] instanceof Conversation;
        }))->once();

        $game->shouldReceive('setTemplateVar')->with('PM_NAVIGATION', Mockery::on(function (array $value): bool {
            return count($value) === 1
                && $value[0]['page'] === 1
                && $value[0]['mark'] === 0
                && $value[0]['cssclass'] === 'pages selected';
        }))->once();

        $this->subject->setTemplateVariables($game);
    }
}
