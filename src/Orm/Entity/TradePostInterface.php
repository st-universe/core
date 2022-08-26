<?php

namespace Stu\Orm\Entity;

interface TradePostInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): TradePostInterface;

    public function getName(): string;

    public function setName(string $name): TradePostInterface;

    public function getDescription(): string;

    public function setDescription(string $description): TradePostInterface;

    public function getShipId(): int;

    public function setShip(ShipInterface $ship): TradePostInterface;

    public function getTradeNetwork(): int;

    public function setTradeNetwork(int $tradeNetwork): TradePostInterface;

    public function getLevel(): int;

    public function setLevel(int $level): TradePostInterface;

    public function getTransferCapacity(): int;

    public function setTransferCapacity(int $transferCapacity): TradePostInterface;

    public function getStorage(): int;

    public function setStorage(int $storage): TradePostInterface;

    public function getShip(): ShipInterface;

    public function calculateLicenceCost(): int;

    public function getLicenceCostGood(): CommodityInterface;
}