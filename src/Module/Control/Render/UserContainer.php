<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

final class UserContainer
{
    private int $id;
    private string $css;
    private bool $hasStationsNavigation;
    private bool $showDeals;

    public function __construct(
        int $id,
        string $css,
        bool $hasStationsNavigation,
        bool $showDeals
    ) {
        $this->id = $id;
        $this->css = $css;
        $this->hasStationsNavigation = $hasStationsNavigation;
        $this->showDeals = $showDeals;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCss(): string
    {
        return $this->css;
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
