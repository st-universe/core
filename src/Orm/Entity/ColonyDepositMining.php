<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyDepositMiningRepository")
 * @Table(
 *     name="stu_colony_deposit_mining"
 * )
 **/
class ColonyDepositMining implements ColonyDepositMiningInterface
{
    /**
     * @Id
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id;

    /**
     * @Id
     * @Column(type="integer")
     *
     * @var int
     */
    private $colony_id;

    /**
     * @Id
     * @Column(type="integer")
     *
     * @var int
     */
    private $commodity_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $amount_left;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var ColonyInterface
     *
     * @ManyToOne(targetEntity="Colony")
     * @JoinColumn(name="colony_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ColonyDepositMiningInterface
    {
        $this->user = $user;
        $this->user_id = $user->getId();

        return $this;
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): ColonyDepositMiningInterface
    {
        $this->colony = $colony;
        $this->colony_id = $colony->getId();

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function setCommodity(CommodityInterface $commodity): ColonyDepositMiningInterface
    {
        $this->commodity = $commodity;
        $this->commodity_id = $commodity->getId();

        return $this;
    }

    public function getAmountLeft(): int
    {
        return $this->amount_left;
    }

    public function setAmountLeft(int $amountLeft): ColonyDepositMiningInterface
    {
        $this->amount_left = $amountLeft;

        return $this;
    }

    public function isEnoughLeft(int $neededAmount): bool
    {
        return $this->getAmountLeft() > $neededAmount;
    }
}
