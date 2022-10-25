<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;
}
