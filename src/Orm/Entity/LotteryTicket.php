<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\LotteryTicketRepository")
 * @Table(
 *     name="stu_lottery_ticket"
 * )
 **/
class LotteryTicket implements LotteryTicketInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id;

    /** @Column(type="string") */
    private $period;

    /** @Column(type="boolean", nullable=true) */
    private $is_winner;


    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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
