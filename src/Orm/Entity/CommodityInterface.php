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

    public function isTradeable(): bool;

    public function isBeamable(UserInterface $user = null, UserInterface $targetUser = null): bool;

    public function isSaveable(): bool;

    public function isBoundToAccount(): bool;

    public function isShuttle(): bool;

    public function isWorkbee(): bool;

    public function isIllegal(int $network): bool;

    public function getTransferCount(): int;
}
