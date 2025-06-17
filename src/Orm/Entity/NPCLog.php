<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\NPCLogRepository;

#[Table(name: 'stu_npc_log')]
#[Entity(repositoryClass: NPCLogRepository::class)]
class NPCLog implements NPCLogInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $source_user_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_id = null;



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
    public function setText(string $text): NPCLogInterface
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
    public function setDate(int $date): NPCLogInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getSourceUserId(): ?int
    {
        return $this->source_user_id;
    }

    #[Override]
    public function setSourceUserId(int $sourceuserId): NPCLogInterface
    {
        $this->source_user_id = $sourceuserId;

        return $this;
    }

    #[Override]
    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    #[Override]
    public function setFactionId(?int $factionId): NPCLogInterface
    {
        $this->faction_id = $factionId;

        return $this;
    }
}
