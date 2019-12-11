<?php

namespace Stu\Orm\Entity;

interface ColonyStorageInterface
{
    public function getId(): int;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonyStorageInterface;

    public function getGoodId(): int;

    public function setGoodId(int $commodityId): ColonyStorageInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): ColonyStorageInterface;

    /**
     * @deprecated
     */
    public function getGood(): CommodityInterface;

    /**
     * @deprecated
     */
    public function setGood(CommodityInterface $commodity): ColonyStorageInterface;

    public function getCommodity(): CommodityInterface;

    public function setCommodity(CommodityInterface $commodity): ColonyStorageInterface;
}
