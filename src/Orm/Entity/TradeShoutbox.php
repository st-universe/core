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

#[Table(name: 'stu_trade_shoutbox')]
#[Index(name: 'trade_network_date_idx', columns: ['trade_network_id', 'date'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\TradeShoutboxRepository')]
class TradeShoutbox implements TradeShoutboxInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'smallint')]
    private int $trade_network_id = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'string')]
    private string $message = '';

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

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
