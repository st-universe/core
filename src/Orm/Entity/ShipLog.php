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
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipLogRepository")
 * @Table(
 *     name="stu_ship_log",
 *     indexes={
 *         @Index(name="ship_log_ship_idx", columns={"ship_id"})
 *     }
 * )
 **/
class ShipLog implements ShipLogInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $ship_id;

    /**
     * @Column(type="text")
     *
     */
    private string $text = '';

    /**
     * @Column(type="integer")
     *
     */
    private int $date;

    /**
     * @Column(type="boolean", options={"default": false})
     *
     */
    private bool $is_private = false;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $deleted;

    /**
     *
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ShipInterface $ship = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setShip(ShipInterface $ship): ShipLogInterface
    {
        $this->ship = $ship;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): ShipLogInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): ShipLogInterface
    {
        $this->date = $date;

        return $this;
    }

    public function setDeleted(int $timestamp): ShipLogInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
