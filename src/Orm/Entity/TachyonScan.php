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
use Override;
use Stu\Orm\Repository\TachyonScanRepository;

#[Table(name: 'stu_tachyon_scan')]
#[Index(name: 'tachyon_scan_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: TachyonScanRepository::class)]
class TachyonScan implements TachyonScanInterface
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

    #[Column(type: 'integer', nullable: true)]
    private ?int $map_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $starsystem_map_id = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'Location')]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private LocationInterface $location;

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
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): TachyonScanInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getScanTime(): int
    {
        return $this->scan_time;
    }
    #[Override]
    public function setScanTime(int $scanTime): TachyonScanInterface
    {
        $this->scan_time = $scanTime;
        return $this;
    }

    #[Override]
    public function getLocation(): LocationInterface
    {
        return $this->location;
    }

    #[Override]
    public function setLocation(LocationInterface $location): TachyonScanInterface
    {
        $this->location = $location;

        return $this;
    }
}
