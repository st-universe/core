<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(
 *     name="stu_planets_commodity",
 *     indexes={
 *         @Index(name="planet_classes_idx", columns={"planet_classes_id"})
 *     }
 * )
 **/
class PlanetCommodity implements PlanetCommodityInterface
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
    private $planet_classes_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $commodity_id = 0;

    /**
     * @Column(type="smallint")
     *
     * @var int
     */
    private $count = 0;
}
