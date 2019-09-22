<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity
 * @Table(
 *     name="stu_user_map",
 *     indexes={
 *     }
 * )
 **/
class UserMap implements UserMapInterface
{
    /** @Id @Column(type="guid", unique=true) @GeneratedValue(strategy="UUID") * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") * */
    private $cx = 0;

    /** @Column(type="integer") * */
    private $cy = 0;

    /** @Column(type="integer") * */
    private $map_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

}
