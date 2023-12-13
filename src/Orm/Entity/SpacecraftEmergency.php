<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_spacecraft_emergency')]
#[Index(name: 'spacecraft_emergency_ship_idx', columns: ['ship_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\SpacecraftEmergencyRepository')]
class SpacecraftEmergency implements SpacecraftEmergencyInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $ship_id = 0;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): SpacecraftEmergencyInterface
    {
        $this->ship = $ship;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): SpacecraftEmergencyInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): SpacecraftEmergencyInterface
    {
        $this->date = $date;

        return $this;
    }

    public function setDeleted(int $timestamp): SpacecraftEmergencyInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
