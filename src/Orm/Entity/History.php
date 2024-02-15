<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\History\HistoryTypeEnum;

#[Table(name: 'stu_history')]
#[Index(name: 'type_idx', columns: ['type'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\HistoryRepository')]
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
    private ?int $user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $source_user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $target_user_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): HistoryInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): HistoryInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): HistoryTypeEnum
    {
        return $this->type;
    }

    public function setType(HistoryTypeEnum $type): HistoryInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): HistoryInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getSourceUserId(): ?int
    {
        return $this->source_user_id;
    }

    public function setSourceUserId(int $sourceuserId): HistoryInterface
    {
        $this->source_user_id = $sourceuserId;

        return $this;
    }

    public function getTargetUserId(): ?int
    {
        return $this->target_user_id;
    }

    public function setTargetUserId(int $targetuserId): HistoryInterface
    {
        $this->target_user_id = $targetuserId;

        return $this;
    }
}