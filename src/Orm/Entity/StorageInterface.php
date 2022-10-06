<?php

namespace Stu\Orm\Entity;

interface StorageInterface
{
    public function getId(): int;

    public function setUserId(int $userId): StorageInterface;

    public function getCommodityId(): int;

    public function getAmount(): int;

    public function setAmount(int $amount): StorageInterface;

    public function getCommodity(): CommodityInterface;

    public function setCommodity(CommodityInterface $commodity): StorageInterface;

    public function getColony(): ?ColonyInterface;

    public function setColony(ColonyInterface $colony): StorageInterface;

    public function getShip(): ?ShipInterface;

    public function setShip(ShipInterface $ship): StorageInterface;

    public function getTradePost(): ?TradePostInterface;

    public function setTradePost(TradePostInterface $tradePost): StorageInterface;

    public function getTradeOffer(): ?TradeOfferInterface;

    public function setTradeOffer(TradeOfferInterface $tradeOffer): StorageInterface;
}
