<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpRoleRepository")
 * @Table(
 *     name="stu_rumps_roles",
 *     indexes={
 *     }
 * )
 **/
class ShipRumpRole implements ShipRumpRoleInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") */
    private $name = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipRumpRoleInterface
    {
        $this->name = $name;

        return $this;
    }
}
