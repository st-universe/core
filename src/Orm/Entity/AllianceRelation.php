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
use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\AllianceRelationRepository;

#[Table(name: 'stu_alliances_relations')]
#[Index(name: 'alliance_relation_idx', columns: ['alliance_id', 'recipient'])]
#[Entity(repositoryClass: AllianceRelationRepository::class)]
#[TruncateOnGameReset]
class AllianceRelation
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint')]
    private int $type = 0;

    #[Column(type: 'integer')]
    private int $alliance_id = 0;

    #[Column(type: 'integer')]
    private int $recipient = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'alliance_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Alliance $alliance;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'recipient', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Alliance $opponent;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): AllianceRelation
    {
        $this->type = $type;
        return $this;
    }

    public function getAllianceId(): int
    {
        return $this->alliance_id;
    }

    public function getOpponentId(): int
    {
        return $this->recipient;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): AllianceRelation
    {
        $this->date = $date;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->getDate() === 0;
    }

    public function isWar(): bool
    {
        return $this->getType() === AllianceEnum::ALLIANCE_RELATION_WAR;
    }

    /**
     * @return array<array{name: string, value: int}>
     */
    public function getPossibleTypes(): array
    {
        $ret = [];
        if ($this->getType() != AllianceEnum::ALLIANCE_RELATION_FRIENDS) {
            $ret[] = ["name" => "Freundschaft", "value" => AllianceEnum::ALLIANCE_RELATION_FRIENDS];
        }
        if ($this->getType() != AllianceEnum::ALLIANCE_RELATION_ALLIED) {
            $ret[] = ["name" => "Bündnis", "value" => AllianceEnum::ALLIANCE_RELATION_ALLIED];
        }
        if ($this->getType() != AllianceEnum::ALLIANCE_RELATION_TRADE) {
            $ret[] = ["name" => "Handelsabkommen", "value" => AllianceEnum::ALLIANCE_RELATION_TRADE];
        }
        if ($this->getType() != AllianceEnum::ALLIANCE_RELATION_TRADE) {
            $ret[] = ["name" => "Vasall", "value" => AllianceEnum::ALLIANCE_RELATION_VASSAL];
        }
        return $ret;
    }

    public function getAlliance(): Alliance
    {
        return $this->alliance;
    }

    public function setAlliance(Alliance $alliance): AllianceRelation
    {
        $this->alliance = $alliance;

        return $this;
    }

    public function getOpponent(): Alliance
    {
        return $this->opponent;
    }

    public function setOpponent(Alliance $opponent): AllianceRelation
    {
        $this->opponent = $opponent;

        return $this;
    }

    /**
     * @deprecated Move into AllianceEnum
     */
    public function getTypeDescription(): string
    {
        return match ($this->getType()) {
            AllianceEnum::ALLIANCE_RELATION_WAR => 'Krieg',
            AllianceEnum::ALLIANCE_RELATION_PEACE => 'Friedensabkommen',
            AllianceEnum::ALLIANCE_RELATION_FRIENDS => 'Freundschaftabkommen',
            AllianceEnum::ALLIANCE_RELATION_ALLIED => 'Bündnis',
            AllianceEnum::ALLIANCE_RELATION_TRADE => 'Handelsabkommen',
            AllianceEnum::ALLIANCE_RELATION_VASSAL => 'Vasall',
            default => '',
        };
    }
}
