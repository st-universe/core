<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

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
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $prestige = 0;

    /**
     * @Column(type="text")
     *
     * @var string
     */
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
