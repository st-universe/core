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
use Stu\Component\Map\MapEnum;
use Stu\Orm\Repository\LayerRepository;

#[Table(name: 'stu_layer')]
#[Entity(repositoryClass: LayerRepository::class)]
class Layer
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'integer')]
    private int $width;

    #[Column(type: 'integer')]
    private int $height;

    #[Column(type: 'boolean')]
    private bool $is_hidden;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_finished = null;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_encoded = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $award_id = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_colonizable = null;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_noobzone = null;

    #[ManyToOne(targetEntity: Award::class)]
    #[JoinColumn(name: 'award_id', referencedColumnName: 'id')]
    private ?Award $award = null;


    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    public function isFinished(): bool
    {
        if ($this->is_finished === null) {
            return false;
        }

        return $this->is_finished;
    }

    public function isEncoded(): bool
    {
        if ($this->is_encoded === null) {
            return false;
        }

        return $this->is_encoded;
    }

    public function getAward(): ?Award
    {
        return $this->award;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Layer
    {
        $this->description = $description;

        return $this;
    }

    public function isColonizable(): bool
    {
        if ($this->is_colonizable === null) {
            return false;
        }

        return $this->is_colonizable;
    }

    public function isNoobzone(): bool
    {
        if ($this->is_noobzone === null) {
            return false;
        }

        return $this->is_noobzone;
    }

    public function getSectorsHorizontal(): int
    {
        return (int)ceil($this->getWidth() / MapEnum::FIELDS_PER_SECTION);
    }

    public function getSectorsVertical(): int
    {
        return (int)ceil($this->getHeight() / MapEnum::FIELDS_PER_SECTION);
    }

    public function getSectorCount(): int
    {
        return $this->getSectorsVertical() * $this->getSectorsHorizontal();
    }

    public function getSectorId(int $mapCx, int $mapCy): int
    {
        return $mapCx + ($mapCy - 1) * $this->getSectorsHorizontal();
    }
}
