<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;


use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_buoy')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\BuoyRepository')]
class Buoy implements BuoyInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'text')]
    private string $text;

    #[Column(type: 'integer', nullable: true)]
    private ?int $map_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $sys_map_id = null;

    #[ManyToOne(targetEntity: 'Map')]
    #[JoinColumn(name: 'map_id', referencedColumnName: 'id')]
    private ?MapInterface $map = null;

    #[ManyToOne(targetEntity: 'StarSystemMap')]
    #[JoinColumn(name: 'sys_map_id', referencedColumnName: 'id')]
    private ?StarSystemMapInterface $systemMap = null;

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
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    #[Override]
    public function getText(): string
    {
        return $this->text;
    }

    #[Override]
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    #[Override]
    public function getMapId(): ?int
    {
        return $this->map_id;
    }

    #[Override]
    public function setMapId(?int $map_id): void
    {
        $this->map_id = $map_id;
    }

    #[Override]
    public function getSysMapId(): ?int
    {
        return $this->sys_map_id;
    }

    #[Override]
    public function setSysMapId(?int $sys_map_id): void
    {
        $this->sys_map_id = $sys_map_id;
    }

    #[Override]
    public function getMap(): ?MapInterface
    {
        return $this->map;
    }

    #[Override]
    public function setMap(?MapInterface $map): void
    {
        $this->map = $map;
    }

    #[Override]
    public function getSystemMap(): ?StarSystemMapInterface
    {
        return $this->systemMap;
    }

    #[Override]
    public function setSystemMap(?StarSystemMapInterface $systemMap): void
    {
        $this->systemMap = $systemMap;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }
}
