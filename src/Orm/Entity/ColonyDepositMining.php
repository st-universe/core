<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\ColonyDepositMiningRepository;

#[Table(name: 'stu_colony_deposit_mining')]
#[Entity(repositoryClass: ColonyDepositMiningRepository::class)]
class ColonyDepositMining
{
    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Id]
    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Colony $colony;

    #[Id]
    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer')]
    private int $amount_left;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): ColonyDepositMining
    {
        $this->user = $user;

        return $this;
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): ColonyDepositMining
    {
        $this->colony = $colony;

        return $this;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(Commodity $commodity): ColonyDepositMining
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getAmountLeft(): int
    {
        return $this->amount_left;
    }

    public function setAmountLeft(int $amountLeft): ColonyDepositMining
    {
        $this->amount_left = $amountLeft;

        return $this;
    }

    public function isEnoughLeft(int $neededAmount): bool
    {
        return $this->getAmountLeft() >= $neededAmount;
    }
}
