<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeTransferRepository")
 * @Table(
 *     name="stu_trade_transfers",
 *     indexes={
 *         @Index(name="post_user_idx", columns={"posts_id", "user_id"})
 *     }
 * )
 **/
class TradeTransfer implements TradeTransferInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $posts_id = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") * */
    private $count = 0;

    /** @Column(type="integer") * */
    private $date = 0;

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

    public function setUserId(int $userId): TradeTransferInterface
    {
        $this->user_id = $userId;

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
}
