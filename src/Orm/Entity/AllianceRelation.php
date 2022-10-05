<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Alliance\AllianceEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AllianceRelationRepository")
 * @Table(
 *     name="stu_alliances_relations",
 *     indexes={
 *         @Index(name="alliance_relation_idx", columns={"alliance_id","recipient"})
 *     }
 * )
 **/
class AllianceRelation implements AllianceRelationInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="smallint") * */
    private $type = 0;

    /** @Column(type="integer") * */
    private $alliance_id = 0;

    /** @Column(type="integer") * */
    private $recipient = 0;

    /** @Column(type="integer") * */
    private $date = 0;

    /**
     * @ManyToOne(targetEntity="Alliance")
     * @JoinColumn(name="alliance_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $alliance;

    /**
     * @ManyToOne(targetEntity="Alliance")
     * @JoinColumn(name="recipient", referencedColumnName="id", onDelete="CASCADE")
     */
    private $opponent;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): AllianceRelationInterface
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

    public function setDate(int $date): AllianceRelationInterface
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
        if ($this->getType() != AllianceEnum::ALLIANCE_RELATION_TRADE) {
                $ret[] = ["name" => "Vasall", "value" => AllianceEnum::ALLIANCE_RELATION_VASSAL];
        }
        return $ret;
    }

    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    public function setAlliance(AllianceInterface $alliance): AllianceRelationInterface
    {
        $this->alliance = $alliance;

        return $this;
    }

    public function getOpponent(): AllianceInterface
    {
        return $this->opponent;
    }

    public function setOpponent(AllianceInterface $opponent): AllianceRelationInterface
    {
        $this->opponent = $opponent;

        return $this;
    }

    public function getTypeDescription(): string
    {
        switch ($this->getType()) {
            case AllianceEnum::ALLIANCE_RELATION_WAR:
                return 'Krieg';
            case AllianceEnum::ALLIANCE_RELATION_PEACE:
                return 'Friedensabkommen';
            case AllianceEnum::ALLIANCE_RELATION_FRIENDS:
                return 'Freundschaftabkommen';
            case AllianceEnum::ALLIANCE_RELATION_ALLIED:
                return 'Bündnis';
            case AllianceEnum::ALLIANCE_RELATION_TRADE:
                    return 'Handelsabkommen';
            case AllianceEnum::ALLIANCE_RELATION_VASSAL:
                    return 'Vasall';
        }
        return '';
    }
}