<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_lottery_ticket')]
#[Index(name: 'lottery_ticket_period_idx', columns: ['period'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\LotteryTicketRepository')]
class LotteryTicket implements LotteryTicketInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'string')]
    private string $period;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_winner = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): LotteryTicketInterface
    {
        $this->user = $user;
        $this->user_id = $user->getId();

        return $this;
    }

    public function getPeriod(): string
    {
        return $this->period;
    }

    public function setPeriod(string $period): LotteryTicketInterface
    {
        $this->period = $period;

        return $this;
    }

    public function setIsWinner(bool $isWinner): bool
    {
        return $this->is_winner = $isWinner;
    }
}
