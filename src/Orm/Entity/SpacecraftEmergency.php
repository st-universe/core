<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\SpacecraftEmergencyRepository;

#[Table(name: 'stu_spacecraft_emergency')]
#[Entity(repositoryClass: SpacecraftEmergencyRepository::class)]
class SpacecraftEmergency
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $spacecraft_id = 0;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Spacecraft $spacecraft;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSpacecraft(): Spacecraft
    {
        return $this->spacecraft;
    }

    public function setSpacecraft(Spacecraft $spacecraft): SpacecraftEmergency
    {
        $this->spacecraft = $spacecraft;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): SpacecraftEmergency
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): SpacecraftEmergency
    {
        $this->date = $date;

        return $this;
    }

    public function setDeleted(int $timestamp): SpacecraftEmergency
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
