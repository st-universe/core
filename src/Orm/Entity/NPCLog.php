<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\NPCLogRepository;

#[Table(name: 'stu_npc_log')]
#[Entity(repositoryClass: NPCLogRepository::class)]
#[TruncateOnGameReset]
class NPCLog
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



    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): NPCLog
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): NPCLog
    {
        $this->date = $date;

        return $this;
    }

    public function getSourceUserId(): ?int
    {
        return $this->source_user_id;
    }

    public function setSourceUserId(int $sourceuserId): NPCLog
    {
        $this->source_user_id = $sourceuserId;

        return $this;
    }

    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    public function setFactionId(?int $factionId): NPCLog
    {
        $this->faction_id = $factionId;

        return $this;
    }
}
