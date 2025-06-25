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
use Override;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Orm\Repository\HistoryRepository;

#[Table(name: 'stu_history')]
#[Index(name: 'type_idx', columns: ['type'])]
#[Entity(repositoryClass: HistoryRepository::class)]
class History implements HistoryInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'smallint', length: 1, enumType: HistoryTypeEnum::class)]
    private HistoryTypeEnum $type = HistoryTypeEnum::OTHER;

    #[Column(type: 'integer', nullable: true)]
    private ?int $source_user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $target_user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $location_id = null;

    #[ManyToOne(targetEntity: Location::class)]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private ?LocationInterface $location;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getText(): string
    {
        return $this->text;
    }

    #[Override]
    public function setText(string $text): HistoryInterface
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
    public function setDate(int $date): HistoryInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getType(): HistoryTypeEnum
    {
        return $this->type;
    }

    #[Override]
    public function setType(HistoryTypeEnum $type): HistoryInterface
    {
        $this->type = $type;

        return $this;
    }

    #[Override]
    public function getSourceUserId(): ?int
    {
        return $this->source_user_id;
    }

    #[Override]
    public function setSourceUserId(int $sourceuserId): HistoryInterface
    {
        $this->source_user_id = $sourceuserId;

        return $this;
    }

    #[Override]
    public function getTargetUserId(): ?int
    {
        return $this->target_user_id;
    }

    #[Override]
    public function setTargetUserId(int $targetuserId): HistoryInterface
    {
        $this->target_user_id = $targetuserId;

        return $this;
    }

    #[Override]
    public function getLocation(): ?LocationInterface
    {
        return $this->location;
    }

    #[Override]
    public function setLocation(?LocationInterface $location): HistoryInterface
    {
        $this->location = $location;

        return $this;
    }
}
