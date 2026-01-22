<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\AllianceJobRepository;

#[Table(name: 'stu_alliances_jobs')]
#[Entity(repositoryClass: AllianceJobRepository::class)]
#[TruncateOnGameReset]
class AllianceJob
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: Alliance::class, inversedBy: 'jobs')]
    #[JoinColumn(name: 'alliance_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Alliance $alliance;

    #[Column(type: 'string', nullable: true)]
    private ?string $title = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $sort = null;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $is_founder_permission = false;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $is_successor_permission = false;

    #[Column(type: 'boolean', options: ['default' => false])]
    private bool $is_diplomatic_permission = false;

    /**
     * @var ArrayCollection<int, AllianceMemberJob>
     */
    #[OneToMany(targetEntity: AllianceMemberJob::class, mappedBy: 'job', cascade: ['remove'])]
    private Collection $memberAssignments;

    /**
     * @var ArrayCollection<int, AllianceJobPermission>
     */
    #[OneToMany(targetEntity: AllianceJobPermission::class, mappedBy: 'job', cascade: ['persist', 'remove'])]
    private Collection $permissions;

    public function __construct()
    {
        $this->memberAssignments = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAlliance(): Alliance
    {
        return $this->alliance;
    }

    public function setAlliance(Alliance $alliance): AllianceJob
    {
        $this->alliance = $alliance;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): AllianceJob
    {
        $this->title = $title;
        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): AllianceJob
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return Collection<int, AllianceMemberJob>
     */
    public function getMemberAssignments(): Collection
    {
        return $this->memberAssignments;
    }

    /**
     * @return Collection<int, AllianceJobPermission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function hasPermission(int $permissionType): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getPermission() === $permissionType) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<User>
     */
    public function getUsers(): array
    {
        return array_map(
            fn(AllianceMemberJob $assignment) => $assignment->getUser(),
            $this->memberAssignments->toArray()
        );
    }

    public function hasUser(User $user): bool
    {
        foreach ($this->memberAssignments as $assignment) {
            if ($assignment->getUser()->getId() === $user->getId()) {
                return true;
            }
        }
        return false;
    }

    public function getUser(): ?User
    {
        $first = $this->memberAssignments->first();
        return $first !== false ? $first->getUser() : null;
    }

    public function getUserId(): ?int
    {
        $user = $this->getUser();
        return $user !== null ? $user->getId() : null;
    }
}
