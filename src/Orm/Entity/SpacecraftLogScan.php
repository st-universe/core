<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\SpacecraftLogScanRepository;

#[Table(name: 'stu_spacecraft_log_scan')]
#[Index(name: 'spacecraft_log_scan_spacecraft_idx', columns: ['spacecraft_id'])]
#[Entity(repositoryClass: SpacecraftLogScanRepository::class)]
class SpacecraftLogScan
{
    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Id]
    #[Column(type: 'integer')]
    private int $spacecraft_id = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserId(): int
    {
        return $this->user->getId();
    }

    public function setUser(User $user): SpacecraftLogScan
    {
        $this->user = $user;

        return $this;
    }

    public function getSpacecraftId(): int
    {
        return $this->spacecraft_id;
    }

    public function setSpacecraftId(int $spacecraftId): SpacecraftLogScan
    {
        $this->spacecraft_id = $spacecraftId;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): SpacecraftLogScan
    {
        $this->date = $date;

        return $this;
    }
}
