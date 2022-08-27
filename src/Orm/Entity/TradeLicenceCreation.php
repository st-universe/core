<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeCreateLicenceRepository")
 * @Table(
 *     name="stu_trade_licences_creation",
 *     indexes={
 *         @Index(name="trade_post_idx", columns={"posts_id"})
 *     }
 * )
 **/
class TradeLicenceCreation implements TradeLicenceCreationInterface
//TODO rename to TradeLicenseInfo.... :D
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $posts_id = 0;

    /** @Column(type="integer") * */
    private $goods_id = 0;

    /** @Column(type="integer") * */
    private $amount = 0;

    /** @Column(type="integer") * */
    private $days = 0;

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

    public function setTradePostId(int $posts_id): TradeLicenceCreationInterface
    {
        $this->posts_id = $posts_id;

        return $this;
    }

    public function getGoodsId(): int
    {
        return $this->goods_id;
    }

    public function setGoodsId(int $goods_id): TradeLicenceCreationInterface
    {
        $this->goods_id = $goods_id;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): TradeLicenceCreationInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeLicenceCreationInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function setDays(int $days): TradeLicenceCreationInterface
    {
        $this->days = $days;

        return $this;
    }
}
