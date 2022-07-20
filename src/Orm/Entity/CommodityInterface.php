<?php

namespace Stu\Orm\Entity;

interface CommodityInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): CommodityInterface;

    public function getSort(): int;

    public function setSort(int $sort): CommodityInterface;

    public function getView(): bool;

    public function setView(bool $view): CommodityInterface;

    public function getType(): int;

    public function setType(int $typeId): CommodityInterface;

    public function getNPCgood(): bool;

    public function setNPCgood(bool $npc_good): CommodityInterface;

    public function isTradeable(): bool;

    public function isBeamable(): bool;

    public function isSaveable(): bool;

    public function isShuttle(): bool;

    public function isWorkbee(): bool;

    public function isIllegal($network): bool;

    public function getTransferCount(): int;
}
