<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
    /** @Id @Column(type="integer") @GeneratedValue * */
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

    public function setAllianceId(int $allianceId): AllianceRelationInterface
    {
        $this->alliance_id = $allianceId;
        return $this;
    }

    public function getRecipientId(): int
    {
        return $this->recipient;
    }

    public function setRecipientId(int $recipientId): AllianceRelationInterface
    {
        $this->recipient = $recipientId;
        return $this;
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

    public function getOpponent(): AllianceInterface
    {
        return $this->opponent;
    }

    public function isWar(): bool
    {
        return $this->getType() === ALLIANCE_RELATION_WAR;
    }

    public function getPossibleTypes(): array
    {
        $ret = [];
        if ($this->getType() != ALLIANCE_RELATION_FRIENDS) {
            $ret[] = ["name" => "Freundschaft", "value" => ALLIANCE_RELATION_FRIENDS];
        }
        if ($this->getType() != ALLIANCE_RELATION_ALLIED) {
            $ret[] = ["name" => "Bündnis", "value" => ALLIANCE_RELATION_ALLIED];
        }
        return $ret;
    }

    public function offerIsSend(): bool
    {
        return $this->getAllianceId() == currentUser()->getAllianceId();
    }

    /**
     * @deprecated
     */
    public function getRecipient(): AllianceInterface
    {
        return $this->getOpponent();
    }

    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    public function getTypeDescription(): string
    {
        switch ($this->getType()) {
            case ALLIANCE_RELATION_WAR:
                return 'Krieg';
            case ALLIANCE_RELATION_PEACE:
                return 'Friedensabkommen';
            case ALLIANCE_RELATION_FRIENDS:
                return 'Freundschaftabkommen';
            case ALLIANCE_RELATION_ALLIED:
                return 'Bündnis';
        }
        return '';
    }
}
