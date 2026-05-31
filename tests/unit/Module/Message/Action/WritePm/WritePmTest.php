<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\View\ShowWriteQuickPmResponse\ShowWriteQuickPmResponse;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class WritePmTest extends ActionControllerTestCase
{
    private MockInterface&WritePmRequestInterface $writePmRequest;
    private MockInterface&IgnoreListRepositoryInterface $ignoreListRepository;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&UserRepositoryInterface $userRepository;

    private WritePm $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->writePmRequest = $this->mock(WritePmRequestInterface::class);
        $this->ignoreListRepository = $this->mock(IgnoreListRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new WritePm(
            $this->writePmRequest,
            $this->ignoreListRepository,
            $this->privateMessageSender,
            $this->userRepository
        );
    }

    public function testHandleReturnsQuickPmSuccessResponse(): void
    {
        request::setMockVars([
            'quickPm' => 1
        ]);

        $info = $this->mock(InformationWrapper::class);
        $sender = $this->mock(User::class);
        $recipient = $this->mock(User::class);

        $this->writePmRequest->shouldReceive('getText')
            ->withNoArgs()
            ->once()
            ->andReturn('Hello recipient');
        $this->writePmRequest->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($sender);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);
        $this->game->shouldReceive('setTemplateVar')
            ->with('QUICKPM_SUCCESS', true)
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('QUICKPM_MESSAGE', 'Die Nachricht wurde abgeschickt')
            ->once();
        $this->game->shouldReceive('setView')
            ->with(ShowWriteQuickPmResponse::VIEW_IDENTIFIER)
            ->once();

        $sender->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $recipient->shouldReceive('getId')
            ->withNoArgs()
            ->times(3)
            ->andReturn(3);

        $this->userRepository->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturn($recipient);
        $this->ignoreListRepository->shouldReceive('exists')
            ->with(3, 2)
            ->once()
            ->andReturn(false);
        $this->privateMessageSender->shouldReceive('send')
            ->with(2, 3, 'Hello recipient', PrivateMessageFolderTypeEnum::SPECIAL_MAIN)
            ->once();

        $info->shouldReceive('addInformation')
            ->with('Die Nachricht wurde abgeschickt')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleReturnsQuickPmFailureResponseWithoutSending(): void
    {
        request::setMockVars([
            'quickPm' => 1
        ]);

        $info = $this->mock(InformationWrapper::class);
        $sender = $this->mock(User::class);

        $this->writePmRequest->shouldReceive('getText')
            ->withNoArgs()
            ->once()
            ->andReturn('Hello recipient');
        $this->writePmRequest->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($sender);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);
        $this->game->shouldReceive('setTemplateVar')
            ->with('QUICKPM_SUCCESS', false)
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('QUICKPM_MESSAGE', 'Dieser Siedler existiert nicht')
            ->once();
        $this->game->shouldReceive('setView')
            ->with(ShowWriteQuickPmResponse::VIEW_IDENTIFIER)
            ->once();

        $sender->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->userRepository->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturnNull();
        $this->ignoreListRepository->shouldReceive('exists')
            ->never();
        $this->privateMessageSender->shouldReceive('send')
            ->never();

        $info->shouldReceive('addInformation')
            ->with('Dieser Siedler existiert nicht')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleKeepsRegularPmRedirectAfterSuccess(): void
    {
        request::setMockVars([]);

        $info = $this->mock(InformationWrapper::class);
        $sender = $this->mock(User::class);
        $recipient = $this->mock(User::class);

        $this->writePmRequest->shouldReceive('getText')
            ->withNoArgs()
            ->once()
            ->andReturn('Hello recipient');
        $this->writePmRequest->shouldReceive('getRecipientId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($sender);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);
        $this->game->shouldReceive('setTemplateVar')
            ->never();
        $this->game->shouldReceive('setView')
            ->with(ModuleEnum::PM)
            ->once();

        $sender->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $recipient->shouldReceive('getId')
            ->withNoArgs()
            ->times(3)
            ->andReturn(3);

        $this->userRepository->shouldReceive('find')
            ->with(3)
            ->once()
            ->andReturn($recipient);
        $this->ignoreListRepository->shouldReceive('exists')
            ->with(3, 2)
            ->once()
            ->andReturn(false);
        $this->privateMessageSender->shouldReceive('send')
            ->with(2, 3, 'Hello recipient', PrivateMessageFolderTypeEnum::SPECIAL_MAIN)
            ->once();

        $info->shouldReceive('addInformation')
            ->with('Die Nachricht wurde abgeschickt')
            ->once();

        $this->subject->handle($this->game);
    }
}
