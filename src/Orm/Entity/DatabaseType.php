<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\DatabaseTypeRepository")
 * @Table(
 *     name="stu_database_types",
 *     options={"engine":"InnoDB"}
 * )
 **/
class DatabaseType implements DatabaseTypeInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") * */
    private $description;

    /** @Column(type="string") * */
    private $macro;

    public function getId(): int
    {
        return $this->id;
    }

    public function setDescription(string $description): DatabaseTypeInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setMacro(string $macro): DatabaseTypeInterface
    {
        $this->macro = $macro;

        return $this;
    }

    public function getMacro(): string
    {
        return $this->macro;
    }
}
