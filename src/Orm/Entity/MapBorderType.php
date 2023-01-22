<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\MapBorderTypeRepository")
 * @Table(
 *     name="stu_map_bordertypes"
 * )
 **/
class MapBorderType implements MapBorderTypeInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $faction_id = 0;

    /** @Column(type="string") * */
    private $color = '';

    /** @Column(type="string") * */
    private $description;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $factionId): MapBorderTypeInterface
    {
        $this->faction_id = $factionId;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): MapBorderTypeInterface
    {
        $this->color = $color;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): MapBorderTypeInterface
    {
        $this->description = $description;

        return $this;
    }
}
