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
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\AllianceMemberJobRepository;

#[Table(name: 'stu_alliance_member_job')]
#[Entity(repositoryClass: AllianceMemberJobRepository::class)]
#[TruncateOnGameReset]
class AllianceMemberJob
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: AllianceJob::class, inversedBy: 'memberAssignments')]
    #[JoinColumn(name: 'job_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceJob $job;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): AllianceMemberJob
    {
        $this->user = $user;
        return $this;
    }

    public function getJob(): AllianceJob
    {
        return $this->job;
    }

    public function setJob(AllianceJob $job): AllianceMemberJob
    {
        $this->job = $job;
        return $this;
    }
}
