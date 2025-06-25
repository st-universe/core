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
use Stu\Orm\Repository\UserPirateRoundRepository;

#[Table(name: 'stu_user_pirate_round')]
#[Entity(repositoryClass: UserPirateRoundRepository::class)]
class UserPirateRound implements UserPirateRoundInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $pirate_round_id = 0;

    #[Column(type: 'integer')]
    private int $destroyed_ships = 0;

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: PirateRound::class)]
    #[JoinColumn(name: 'pirate_round_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PirateRoundInterface $pirateRound;

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
    public function setUser(UserInterface $user): UserPirateRoundInterface
    {
        $this->user = $user;
        $this->user_id = $user->getId();
        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function getPirateRoundId(): int
    {
        return $this->pirate_round_id;
    }

    #[Override]
    public function setPirateRound(PirateRoundInterface $pirateRound): UserPirateRoundInterface
    {
        $this->pirateRound = $pirateRound;
        $this->pirate_round_id = $pirateRound->getId();
        return $this;
    }

    #[Override]
    public function getPirateRound(): PirateRoundInterface
    {
        return $this->pirateRound;
    }

    #[Override]
    public function getDestroyedShips(): int
    {
        return $this->destroyed_ships;
    }

    #[Override]
    public function setDestroyedShips(int $destroyedShips): UserPirateRoundInterface
    {
        $this->destroyed_ships = $destroyedShips;
        return $this;
    }

    #[Override]
    public function getPrestige(): int
    {
        return $this->prestige;
    }

    #[Override]
    public function setPrestige(int $prestige): UserPirateRoundInterface
    {
        $this->prestige = $prestige;
        return $this;
    }
}
