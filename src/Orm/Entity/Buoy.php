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
use Override;
use Stu\Orm\Repository\BuoyRepository;

#[Table(name: 'stu_buoy')]
#[Entity(repositoryClass: BuoyRepository::class)]
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

    #[Column(type: 'integer')]
    private int $location_id = 0;

    #[ManyToOne(targetEntity: Location::class)]
    #[JoinColumn(name: 'location_id', nullable: false, referencedColumnName: 'id')]
    private LocationInterface $location;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
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
    public function getLocation(): LocationInterface
    {
        return $this->location;
    }

    #[Override]
    public function setLocation(LocationInterface $location): BuoyInterface
    {
        $this->location = $location;

        return $this;
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
