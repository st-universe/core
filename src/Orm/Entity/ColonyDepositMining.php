<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\ColonyDepositMiningRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_colony_deposit_mining')]
#[Entity(repositoryClass: ColonyDepositMiningRepository::class)]
class ColonyDepositMining implements ColonyDepositMiningInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $user_id;

    #[Id]
    #[Column(type: 'integer')]
    private int $colony_id;

    #[Id]
    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer')]
    private int $amount_left;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyInterface $colony;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): ColonyDepositMiningInterface
    {
        $this->user = $user;
        $this->user_id = $user->getId();

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
        $this->colony_id = $colony->getId();

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
        $this->commodity_id = $commodity->getId();

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
