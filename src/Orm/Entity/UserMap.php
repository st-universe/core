<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserMapRepository")
 * @Table(
 *     name="stu_user_map"
 * )
 **/
class UserMap implements UserMapInterface
{
    /**
     * @Id
     * @Column(type="integer")
     */
    private $user_id = 0;

    /**
     * @Id
     * @Column(type="integer")
     */
    private $layer_id = 0;

    /**
     * @Id
     * @Column(type="integer")
     */
    private $cx = 0;

    /**
     * @Id
     * @Column(type="integer")
     */
    private $cy = 0;

    /** @Column(type="integer") * */
    private $map_id = 0;

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var LayerInterface
     *
     * @ManyToOne(targetEntity="Layer")
     * @JoinColumn(name="layer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $layer;
}
