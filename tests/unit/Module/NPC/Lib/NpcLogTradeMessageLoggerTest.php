<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Lib;

use Mockery;
use Mockery\MockInterface;
use Stu\Orm\Entity\NPCLog;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class NpcLogTradeMessageLoggerTest extends StuTestCase
{
    private MockInterface&NPCLogRepositoryInterface $npcLogRepository;
    private MockInterface&UserRepositoryInterface $userRepository;

    private NpcLogTradeMessageLogger $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->npcLogRepository = $this->mock(NPCLogRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new NpcLogTradeMessageLogger(
            $this->npcLogRepository,
            $this->userRepository
        );
    }

    public function testLogIfNpcInvolvedCreatesAdminViewEntryForRecipientNpc(): void
    {
        $sender = $this->mock(User::class);
        $recipient = $this->mock(User::class);
        $entry = new NPCLog();

        $sender->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Sender');
        $recipient->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Npc');

        $this->userRepository->shouldReceive('find')
            ->with(101)
            ->once()
            ->andReturn($sender);
        $this->userRepository->shouldReceive('find')
            ->with(10)
            ->once()
            ->andReturn($recipient);

        $this->npcLogRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($entry);
        $this->npcLogRepository->shouldReceive('save')
            ->with(Mockery::on(function (NPCLog $savedEntry): bool {
                return $savedEntry->getText() === 'Sender (101) -> Npc (10): test text'
                    && $savedEntry->getSourceUserId() === 10
                    && $savedEntry->getAdminView() === true;
            }))
            ->once();

        $this->subject->logIfNpcInvolved(101, 10, 'test text');
    }

    public function testLogIfNpcInvolvedSkipsWhenNoNpcInvolved(): void
    {
        $this->userRepository->shouldNotReceive('find');
        $this->npcLogRepository->shouldNotReceive('prototype');
        $this->npcLogRepository->shouldNotReceive('save');

        $this->subject->logIfNpcInvolved(101, 102, 'test text');
    }
}
