<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\ColonyDepositMiningRepository;

#[Table(name: 'stu_colony_deposit_mining')]
#[Entity(repositoryClass: ColonyDepositMiningRepository::class)]
class ColonyDepositMining implements ColonyDepositMiningInterface
{
    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Id]
    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyInterface $colony;

    #[Id]
    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer')]
    private int $amount_left;

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): ColonyDepositMiningInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(ColonyInterface $colony): ColonyDepositMiningInterface
    {
        $this->colony = $colony;

        return $this;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function setCommodity(CommodityInterface $commodity): ColonyDepositMiningInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    #[Override]
    public function getAmountLeft(): int
    {
        return $this->amount_left;
    }

    #[Override]
    public function setAmountLeft(int $amountLeft): ColonyDepositMiningInterface
    {
        $this->amount_left = $amountLeft;

        return $this;
    }

    #[Override]
    public function isEnoughLeft(int $neededAmount): bool
    {
        return $this->getAmountLeft() >= $neededAmount;
    }
}
