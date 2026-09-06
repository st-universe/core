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
use LogicException;
use Stu\Component\Alliance\Enum\RelationPermissionDirectionEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Orm\Repository\RelationPermissionRepository;

#[Table(name: 'stu_relation_permissions')]
#[Entity(repositoryClass: RelationPermissionRepository::class)]
class RelationPermission
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'smallint', enumType: RelationPermissionEnum::class)]
    private RelationPermissionEnum $permission;

    #[Column(type: 'smallint', enumType: RelationPermissionDirectionEnum::class)]
    private RelationPermissionDirectionEnum $direction = RelationPermissionDirectionEnum::MUTUAL;

    #[Column(type: 'boolean')]
    private bool $pending = false;

    #[Column(type: 'boolean')]
    private bool $granted = true;

    #[Column(type: 'boolean')]
    private bool $offeredBySource = false;

    #[ManyToOne(targetEntity: Relation::class, inversedBy: 'relationPermissions')]
    #[JoinColumn(name: 'relation_id', referencedColumnName: 'id', nullable: true)]
    private ?Relation $relation = null;

    #[ManyToOne(targetEntity: Contact::class, inversedBy: 'relationPermissions')]
    #[JoinColumn(name: 'contact_id', referencedColumnName: 'id', nullable: true)]
    private ?Contact $contact = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPermission(): RelationPermissionEnum
    {
        return $this->permission;
    }

    public function setPermission(RelationPermissionEnum $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    public function getDirection(): RelationPermissionDirectionEnum
    {
        return $this->direction;
    }

    public function setDirection(RelationPermissionDirectionEnum $direction): self
    {
        $this->direction = $direction;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->pending;
    }

    public function setPending(bool $pending): self
    {
        $this->pending = $pending;
        return $this;
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function setGranted(bool $granted): self
    {
        $this->granted = $granted;
        return $this;
    }

    public function isOfferedBySource(): bool
    {
        return $this->offeredBySource;
    }

    public function setOfferedBySource(bool $offeredBySource): self
    {
        $this->offeredBySource = $offeredBySource;
        return $this;
    }

    public function getRelation(): ?Relation
    {
        return $this->relation;
    }

    public function setRelation(?Relation $relation): self
    {
        $this->relation = $relation;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    public function validateOwner(): void
    {
        if (($this->relation === null) === ($this->contact === null)) {
            throw new LogicException(
                'Eine Berechtigung muss genau einer Relation oder einem Kontakt zugeordnet sein'
            );
        }
    }

    public function isGrantedTo(User $user): bool
    {
        if ($this->direction === RelationPermissionDirectionEnum::MUTUAL) {
            return true;
        }

        return $this->relation?->isRecipientParty($user) ?? false;
    }
}
