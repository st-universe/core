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
use Stu\Orm\Repository\UserPirateRoundRepository;

#[Table(name: 'stu_user_pirate_round')]
#[Entity(repositoryClass: UserPirateRoundRepository::class)]
class UserPirateRound
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
    private User $user;

    #[ManyToOne(targetEntity: PirateRound::class)]
    #[JoinColumn(name: 'pirate_round_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private PirateRound $pirateRound;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUser(User $user): UserPirateRound
    {
        $this->user = $user;
        $this->user_id = $user->getId();
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPirateRoundId(): int
    {
        return $this->pirate_round_id;
    }

    public function setPirateRound(PirateRound $pirateRound): UserPirateRound
    {
        $this->pirateRound = $pirateRound;
        $this->pirate_round_id = $pirateRound->getId();
        return $this;
    }

    public function getPirateRound(): PirateRound
    {
        return $this->pirateRound;
    }

    public function getDestroyedShips(): int
    {
        return $this->destroyed_ships;
    }

    public function setDestroyedShips(int $destroyedShips): UserPirateRound
    {
        $this->destroyed_ships = $destroyedShips;
        return $this;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function setPrestige(int $prestige): UserPirateRound
    {
        $this->prestige = $prestige;
        return $this;
    }
}
