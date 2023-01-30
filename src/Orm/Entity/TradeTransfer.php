<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeTransferRepository")
 * @Table(
 *     name="stu_trade_transfers",
 *     indexes={
 *         @Index(name="trade_transfer_post_user_idx", columns={"posts_id", "user_id"})
 *     }
 * )
 **/
class TradeTransfer implements TradeTransferInterface
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
    private $posts_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $count = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $date = 0;

    /**
     * @var TradePostInterface
     *
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="posts_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradePost;

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

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $tradePostId): TradeTransferInterface
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TradeTransferInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): TradeTransferInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeTransferInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePostInterface $tradePost): TradeTransferInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }
}
