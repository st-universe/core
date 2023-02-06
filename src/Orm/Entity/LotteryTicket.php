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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\LotteryTicketRepository")
 * @Table(
 *     name="stu_lottery_ticket"
 *     indexes={
 *         @Index(name="lottery_ticket_period_idx", columns={"period"})
 *     }
 * )
 **/
class LotteryTicket implements LotteryTicketInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $period;

    /**
     * @Column(type="boolean", nullable=true)
     *
     * @var bool|null
     */
    private $is_winner;

    /**
     * @var UserInterface
     *
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
