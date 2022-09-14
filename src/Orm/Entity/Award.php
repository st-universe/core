<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AwardRepository")
 * @Table(
 *     name="stu_award"
 * )
 **/
class Award implements AwardInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     */
    private $id;

    /** @Column(type="integer") */
    private $prestige = 0;

    /** @Column(type="text") */
    private $description = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
