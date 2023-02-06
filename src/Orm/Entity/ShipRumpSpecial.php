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
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpSpecialRepository")
 * @Table(
 *     name="stu_rumps_specials",
 *     indexes={
 *         @Index(name="rump_special_ship_rump_idx", columns={"rumps_id"})
 *     }
 * )
 **/
class ShipRumpSpecial implements ShipRumpSpecialInterface
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
    private $rumps_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $special = 0;

    /**
     * @ManyToOne(targetEntity="ShipRump", inversedBy="specialAbilities")
     * @JoinColumn(name="rumps_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ShipRumpInterface $shipRump;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipRumpId(): int
    {
        return $this->rumps_id;
    }

    public function setShipRumpId(int $shipRumpId): ShipRumpSpecialInterface
    {
        $this->rumps_id = $shipRumpId;

        return $this;
    }

    public function getSpecialId(): int
    {
        return $this->special;
    }

    public function setSpecialId(int $specialId): ShipRumpSpecialInterface
    {
        $this->special = $specialId;

        return $this;
    }
}
