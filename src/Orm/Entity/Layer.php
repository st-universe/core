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
use Stu\Component\Map\MapEnum;
use Stu\Orm\Repository\LayerRepository;

#[Table(name: 'stu_layer')]
#[Entity(repositoryClass: LayerRepository::class)]
class Layer implements LayerInterface
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

    #[ManyToOne(targetEntity: 'Award')]
    #[JoinColumn(name: 'award_id', referencedColumnName: 'id')]
    private ?AwardInterface $award = null;


    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getWidth(): int
    {
        return $this->width;
    }

    #[Override]
    public function getHeight(): int
    {
        return $this->height;
    }

    #[Override]
    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    #[Override]
    public function isFinished(): bool
    {
        if ($this->is_finished === null) {
            return false;
        }

        return $this->is_finished;
    }

    #[Override]
    public function isEncoded(): bool
    {
        if ($this->is_encoded === null) {
            return false;
        }

        return $this->is_encoded;
    }

    #[Override]
    public function getAward(): ?AwardInterface
    {
        return $this->award;
    }

    #[Override]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(?string $description): LayerInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function isColonizable(): bool
    {
        if ($this->is_colonizable === null) {
            return false;
        }

        return $this->is_colonizable;
    }

    #[Override]
    public function isNoobzone(): bool
    {
        if ($this->is_noobzone === null) {
            return false;
        }

        return $this->is_noobzone;
    }

    #[Override]
    public function getSectorsHorizontal(): int
    {
        return (int)ceil($this->getWidth() / MapEnum::FIELDS_PER_SECTION);
    }

    #[Override]
    public function getSectorsVertical(): int
    {
        return (int)ceil($this->getHeight() / MapEnum::FIELDS_PER_SECTION);
    }

    #[Override]
    public function getSectorCount(): int
    {
        return $this->getSectorsVertical() * $this->getSectorsHorizontal();
    }

    #[Override]
    public function getSectorId(int $mapCx, int $mapCy): int
    {
        return $mapCx + ($mapCy - 1) * $this->getSectorsHorizontal();
    }
}
