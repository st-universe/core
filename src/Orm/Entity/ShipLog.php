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
use Stu\Orm\Repository\ShipLogRepository;

#[Table(name: 'stu_ship_log')]
#[Entity(repositoryClass: ShipLogRepository::class)]
class ShipLog implements ShipLogInterface
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

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $is_private = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: 'Spacecraft')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SpacecraftInterface $spacecraft = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setSpacecraft(SpacecraftInterface $spacecraft): ShipLogInterface
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
    public function setText(string $text): ShipLogInterface
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
    public function setDate(int $date): ShipLogInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function setDeleted(int $timestamp): ShipLogInterface
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
