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
use Override;
use Stu\Orm\Repository\SpacecraftEmergencyRepository;

#[Table(name: 'stu_spacecraft_emergency')]
#[Entity(repositoryClass: SpacecraftEmergencyRepository::class)]
class SpacecraftEmergency implements SpacecraftEmergencyInterface
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

    #[ManyToOne(targetEntity: 'Spacecraft')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftInterface $spacecraft;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getSpacecraft(): SpacecraftInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function setSpacecraft(SpacecraftInterface $spacecraft): SpacecraftEmergencyInterface
    {
        $this->spacecraft = $spacecraft;

        return $this;
    }

    #[Override]
    public function getText(): string
    {
        return $this->text;
    }

    #[Override]
    public function setText(string $text): SpacecraftEmergencyInterface
    {
        $this->text = $text;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): SpacecraftEmergencyInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function setDeleted(int $timestamp): SpacecraftEmergencyInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    #[Override]
    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
