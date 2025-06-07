<?php

namespace Stu\Orm\Entity;

interface FactionInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): FactionInterface;

    public function getDescription(): string;

    public function setDescription(string $description): FactionInterface;

    public function getDarkerColor(): string;

    public function setDarkerColor(string $darkerColor): FactionInterface;

    public function getChooseable(): bool;

    public function setChooseable(bool $chooseable): FactionInterface;

    public function getPlayerLimit(): int;

    public function setPlayerLimit(int $playerLimit): FactionInterface;

    public function getStartBuildingId(): int;

    public function setStartBuildingId(int $startBuildingId): FactionInterface;

    public function getStartResearch(): ?ResearchInterface;

    public function setStartResearch(?ResearchInterface $start_research): FactionInterface;

    public function getStartMap(): ?MapInterface;

    public function setStartMap(?MapInterface $start_map): FactionInterface;

    public function getCloseCombatScore(): ?int;

    public function getPrimaryEffectCommodity(): ?CommodityInterface;

    public function getSecondaryEffectCommodity(): ?CommodityInterface;

    public function getWelcomeMessage(): ?string;

    public function setWelcomeMessage(string $welcome_message): FactionInterface;
}
