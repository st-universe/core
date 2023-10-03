<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

final class UserContainer
{
    private int $id;
    private string $avatar;
    private string $name;
    private ?int $factionId;
    private string $css;
    private int $prestige;
    private bool $hasStationsNavigation;
    private bool $showDeals;

    public function __construct(
        int $id,
        string $avatar,
        string $name,
        ?int $factionId,
        string $css,
        int $prestige,
        bool $hasStationsNavigation,
        bool $showDeals
    ) {
        $this->id = $id;
        $this->avatar = $avatar;
        $this->name = $name;
        $this->factionId = $factionId;
        $this->css = $css;
        $this->prestige = $prestige;
        $this->hasStationsNavigation = $hasStationsNavigation;
        $this->showDeals = $showDeals;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFactionId(): ?int
    {
        return $this->factionId;
    }

    public function getCss(): string
    {
        return $this->css;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function hasStationsNavigation(): bool
    {
        return $this->hasStationsNavigation;
    }

    public function showDeals(): int
    {
        return $this->showDeals ? 1 : 0;
    }
}
