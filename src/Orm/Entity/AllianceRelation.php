<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Alliance\AllianceEnum;

#[Table(name: 'stu_alliances_relations')]
#[Index(name: 'alliance_relation_idx', columns: ['alliance_id', 'recipient'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\AllianceRelationRepository')]
class AllianceRelation implements AllianceRelationInterface
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

    #[ManyToOne(targetEntity: 'Alliance')]
    #[JoinColumn(name: 'alliance_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceInterface $alliance;

    #[ManyToOne(targetEntity: 'Alliance')]
    #[JoinColumn(name: 'recipient', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceInterface $opponent;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getType(): int
    {
        return $this->type;
    }

    #[Override]
    public function setType(int $type): AllianceRelationInterface
    {
        $this->type = $type;
        return $this;
    }

    #[Override]
    public function getAllianceId(): int
    {
        return $this->alliance_id;
    }

    #[Override]
    public function getOpponentId(): int
    {
        return $this->recipient;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): AllianceRelationInterface
    {
        $this->date = $date;
        return $this;
    }

    #[Override]
    public function isPending(): bool
    {
        return $this->getDate() === 0;
    }

    #[Override]
    public function isWar(): bool
    {
        return $this->getType() === AllianceEnum::ALLIANCE_RELATION_WAR;
    }

    #[Override]
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

    #[Override]
    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    #[Override]
    public function setAlliance(AllianceInterface $alliance): AllianceRelationInterface
    {
        $this->alliance = $alliance;

        return $this;
    }

    #[Override]
    public function getOpponent(): AllianceInterface
    {
        return $this->opponent;
    }

    #[Override]
    public function setOpponent(AllianceInterface $opponent): AllianceRelationInterface
    {
        $this->opponent = $opponent;

        return $this;
    }

    #[Override]
    public function getTypeDescription() : string
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
