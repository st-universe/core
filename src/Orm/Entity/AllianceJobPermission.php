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
use Stu\Orm\Repository\AllianceJobPermissionRepository;

#[Table(name: 'stu_alliance_job_permission')]
#[Entity(repositoryClass: AllianceJobPermissionRepository::class)]
#[TruncateOnGameReset]
class AllianceJobPermission
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: AllianceJob::class, inversedBy: 'permissions')]
    #[JoinColumn(name: 'job_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceJob $job;

    #[Column(type: 'integer')]
    private int $permission;

    public function getId(): int
    {
        return $this->id;
    }

    public function getJob(): AllianceJob
    {
        return $this->job;
    }

    public function setJob(AllianceJob $job): AllianceJobPermission
    {
        $this->job = $job;
        return $this;
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function setPermission(int $permission): AllianceJobPermission
    {
        $this->permission = $permission;
        return $this;
    }
}
