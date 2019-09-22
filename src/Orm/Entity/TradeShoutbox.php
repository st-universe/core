<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeShoutboxRepository")
 * @Table(
 *     name="stu_trade_shoutbox",
 *     indexes={
 *         @Index(name="trade_network_date_idx", columns={"trade_network_id", "date"})
 *     }
 * )
 **/
class TradeShoutbox implements TradeShoutboxInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="smallint") * */
    private $trade_network_id = 0;

    /** @Column(type="integer") * */
    private $date = 0;

    /** @Column(type="string") */
    private $message = '';

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTradeNetworkId(): int
    {
        return $this->trade_network_id;
    }

    public function setTradeNetworkId(int $tradeNetworkId): TradeShoutboxInterface
    {
        $this->trade_network_id = $tradeNetworkId;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeShoutboxInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): TradeShoutboxInterface
    {
        $this->message = $message;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TradeShoutboxInterface
    {
        $this->user = $user;
        return $this;
    }
}
