<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\AllianceJobRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_alliances_jobs')]
#[Entity(repositoryClass: AllianceJobRepository::class)]
class AllianceJob implements AllianceJobInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $alliance_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'smallint')]
    private int $type = 0;

    #[ManyToOne(targetEntity: 'Alliance')]
    #[JoinColumn(name: 'alliance_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private AllianceInterface $alliance;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getType(): int
    {
        return $this->type;
    }

    #[Override]
    public function setType(int $type): AllianceJobInterface
    {
        $this->type = $type;

        return $this;
    }

    #[Override]
    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    #[Override]
    public function setAlliance(AllianceInterface $alliance): AllianceJobInterface
    {
        $this->alliance = $alliance;

        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): AllianceJobInterface
    {
        $this->user = $user;
        return $this;
    }
}
