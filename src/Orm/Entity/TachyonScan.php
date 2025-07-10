<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\TachyonScanRepository;

#[Table(name: 'stu_tachyon_scan')]
#[Index(name: 'tachyon_scan_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: TachyonScanRepository::class)]
#[TruncateOnGameReset]
class TachyonScan
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $scan_time = 0;

    #[Column(type: 'integer')]
    private int $location_id = 0;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: Location::class)]
    #[JoinColumn(name: 'location_id', nullable: false, referencedColumnName: 'id')]
    private Location $location;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): TachyonScan
    {
        $this->user = $user;
        return $this;
    }

    public function getScanTime(): int
    {
        return $this->scan_time;
    }
    public function setScanTime(int $scanTime): TachyonScan
    {
        $this->scan_time = $scanTime;
        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): TachyonScan
    {
        $this->location = $location;

        return $this;
    }
}
