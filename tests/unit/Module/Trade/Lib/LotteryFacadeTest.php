<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;
use Stu\StuTestCase;

class LotteryFacadeTest extends StuTestCase
{
    /**
     * @var LotteryTicketRepositoryInterface|MockInterface
     */
    private $lotteryTicketRepository;

    /**
     * @var PrivateMessageSenderInterface|MockInterface
     */
    private $privateMessageSender;

    /**
     * @var StuTime|MockInterface
     */
    private $stuTime;

    private LotteryFacadeInterface $lotteryFacade;

    #[Override]
    public function setUp(): void
    {
        $this->lotteryTicketRepository = $this->mock(LotteryTicketRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->lotteryFacade = new LotteryFacade(
            $this->lotteryTicketRepository,
            $this->privateMessageSender,
            $this->stuTime,
        );
    }

    public function testCreateLotteryTicket(): void
    {
        $user = $this->mock(User::class);
        $ticket = $this->mock(LotteryTicket::class);

        $time = 1672575107;

        $this->lotteryTicketRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($ticket);
        $this->lotteryTicketRepository->shouldReceive('save')
            ->with($ticket)
            ->once();

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn($time);

        $ticket->shouldReceive('setUser')
            ->with($user)
            ->once();
        $ticket->shouldReceive('setPeriod')
            ->with('2393.01')
            ->once();

        $this->lotteryFacade->createLotteryTicket($user, false);
    }

    public function testGetTicketAmount(): void
    {
        $time = 1672575107;

        $this->lotteryTicketRepository->shouldReceive('getAmountByPeriod')
            ->with('2393.01')
            ->once()
            ->andReturn(123);

        $this->stuTime->shouldReceive('time')
            ->once()->andReturn($time);

        $result = $this->lotteryFacade->getTicketAmount(false);

        $this->assertEquals(123, $result);
    }

    public function testGetTicketAmountByUser(): void
    {
        $time = 1672575107;

        $this->lotteryTicketRepository->shouldReceive('getAmountByPeriodAndUser')
            ->with('2393.01', 42)
            ->once()
            ->andReturn(123);

        $this->stuTime->shouldReceive('time')
            ->once()->andReturn($time);

        $result = $this->lotteryFacade->getTicketAmountByUser(42, false);

        $this->assertEquals(123, $result);
    }

    public function testGetTicketsOfLastPeriod(): void
    {
        $time = 1672575107;
        $tickets = [];

        $this->lotteryTicketRepository->shouldReceive('getByPeriod')
            ->with('2392.12')
            ->once()
            ->andReturn($tickets);

        $this->stuTime->shouldReceive('time')
            ->once()->andReturn($time);

        $result = $this->lotteryFacade->getTicketsOfLastPeriod();

        $this->assertSame($tickets, $result);
    }
}
