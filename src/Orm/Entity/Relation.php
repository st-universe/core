<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Relations\Trait\RelationPartyTrait;
use Stu\Component\Alliance\Relations\Trait\RelationPermissionTrait;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\RelationRepository;

#[Table(name: 'stu_relations')]
#[Index(name: 'relation_source_user_idx', columns: ['source_user_id'])]
#[Index(name: 'relation_recipient_user_idx', columns: ['recipient_user_id'])]
#[Index(name: 'relation_source_alliance_idx', columns: ['source_alliance_id'])]
#[Index(name: 'relation_recipient_alliance_idx', columns: ['recipient_alliance_id'])]
#[Entity(repositoryClass: RelationRepository::class)]
#[TruncateOnGameReset]
class Relation
{
    use RelationPartyTrait;
    use RelationPermissionTrait;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint', enumType: AllianceRelationTypeEnum::class)]
    private AllianceRelationTypeEnum $type = AllianceRelationTypeEnum::FRIENDS;

    #[Column(type: 'integer')]
    private int $date = 0;

    /** @var Collection<int, RelationPermission> */
    #[OneToMany(targetEntity: RelationPermission::class, mappedBy: 'relation')]
    private Collection $relationPermissions;

    #[Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $last_edited = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'source_user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $sourceUser = null;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'source_alliance_id', referencedColumnName: 'id', nullable: true)]
    private ?Alliance $sourceAlliance = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'recipient_user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $recipientUser = null;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'recipient_alliance_id', referencedColumnName: 'id', nullable: true)]
    private ?Alliance $recipientAlliance = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): AllianceRelationTypeEnum
    {
        return $this->type;
    }

    public function setType(AllianceRelationTypeEnum $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->date === 0;
    }

    public function isWar(): bool
    {
        return $this->type === AllianceRelationTypeEnum::WAR;
    }

    public function __construct()
    {
        $this->relationPermissions = new ArrayCollection();
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getLastEdited(): ?int
    {
        return $this->last_edited;
    }

    public function setLastEdited(?int $lastEdited): self
    {
        $this->last_edited = $lastEdited;
        return $this;
    }

    public function hasText(): bool
    {
        return $this->text !== null && trim($this->text) !== '';
    }

}
