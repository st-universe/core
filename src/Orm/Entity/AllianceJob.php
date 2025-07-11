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
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
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

    #[Column(type: 'integer')]
    private int $alliance_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'smallint', enumType: AllianceJobTypeEnum::class)]
    private AllianceJobTypeEnum $type = AllianceJobTypeEnum::PENDING;

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'alliance_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Alliance $alliance;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getType(): AllianceJobTypeEnum
    {
        return $this->type;
    }

    public function setType(AllianceJobTypeEnum $type): AllianceJob
    {
        $this->type = $type;

        return $this;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): AllianceJob
    {
        $this->user = $user;
        return $this;
    }
}
