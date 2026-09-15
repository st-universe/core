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
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Orm\Repository\OrionAuctionRepository;

#[Table(name: 'stu_orion_auction')]
#[Entity(repositoryClass: OrionAuctionRepository::class)]
class OrionAuction
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $start;

    #[Column(name: '"end"', type: 'integer')]
    private int $end;

    #[Column(type: 'integer', nullable: true)]
    private ?int $completed_at = null;

    #[Column(type: 'integer')]
    private int $auction_amount = 0;

    #[Column(name: 'winner_name', type: 'string', nullable: true)]
    private ?string $winnerName = null;

    #[ManyToOne(targetEntity: Crew::class)]
    #[JoinColumn(name: 'crew_id', referencedColumnName: 'id', nullable: false)]
    private Crew $crew;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'wanted_commodity_id', referencedColumnName: 'id')]
    private ?Commodity $wantedCommodity = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'winner_id', referencedColumnName: 'id', nullable: true)]
    private ?User $winner = null;

    #[Column(type: 'smallint', enumType: CrewTypeEnum::class)]
    private CrewTypeEnum $image_position = CrewTypeEnum::CREWMAN;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): OrionAuction
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function setEnd(int $end): OrionAuction
    {
        $this->end = $end;

        return $this;
    }

    public function getCompletedAt(): ?int
    {
        return $this->completed_at;
    }

    public function setCompletedAt(int $completedAt): OrionAuction
    {
        $this->completed_at = $completedAt;

        return $this;
    }

    public function getAuctionAmount(): int
    {
        return $this->auction_amount;
    }

    public function setAuctionAmount(int $auctionAmount): OrionAuction
    {
        $this->auction_amount = $auctionAmount;

        return $this;
    }

    public function getWinnerName(): ?string
    {
        return $this->winnerName;
    }

    public function setWinnerName(?string $winnerName): OrionAuction
    {
        $this->winnerName = $winnerName;

        return $this;
    }

    public function getCrew(): Crew
    {
        return $this->crew;
    }

    public function setCrew(Crew $crew): OrionAuction
    {
        $this->crew = $crew;

        return $this;
    }

    public function getWantedCommodity(): ?Commodity
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(?Commodity $wantedCommodity): OrionAuction
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): OrionAuction
    {
        $this->winner = $winner;

        return $this;
    }

    public function getImagePosition(): CrewTypeEnum
    {
        return $this->image_position;
    }

    public function getCrewImageType(): int
    {
        return match ($this->image_position) {
            CrewTypeEnum::CAPTAIN, CrewTypeEnum::COMMAND => 1,
            CrewTypeEnum::TACTIC => 2,
            CrewTypeEnum::SCIENCE => 3,
            CrewTypeEnum::TECHNICAL => 4,
            CrewTypeEnum::NAVIGATION => 5,
            CrewTypeEnum::CREWMAN => 6
        };
    }

    public function setImagePosition(CrewTypeEnum $imagePosition): OrionAuction
    {
        $this->image_position = $imagePosition;

        return $this;
    }
}
