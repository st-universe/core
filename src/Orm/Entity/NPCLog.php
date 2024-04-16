<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;


#[Table(name: 'stu_npc_log')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\NPCLogRepository')]
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



    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): NPCLogInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): NPCLogInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getSourceUserId(): ?int
    {
        return $this->source_user_id;
    }

    public function setSourceUserId(int $sourceuserId): NPCLogInterface
    {
        $this->source_user_id = $sourceuserId;

        return $this;
    }
}
