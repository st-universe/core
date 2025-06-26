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
use Stu\Orm\Repository\ShipLogRepository;

#[Table(name: 'stu_ship_log')]
#[Entity(repositoryClass: ShipLogRepository::class)]
class ShipLog
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $spacecraft_id;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date;

    #[Column(type: 'boolean')]
    private bool $is_private = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Spacecraft $spacecraft = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setSpacecraft(Spacecraft $spacecraft): ShipLog
    {
        $this->spacecraft = $spacecraft;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): ShipLog
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): ShipLog
    {
        $this->date = $date;

        return $this;
    }

    public function setDeleted(int $timestamp): ShipLog
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
