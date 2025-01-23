<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

final class UserContainer
{
    public function __construct(private int $id, private string $avatar, private string $name, private int $factionId, private string $css, private bool $hasStationsNavigation) {}

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

    public function getFactionId(): int
    {
        return $this->factionId;
    }

    public function getCss(): string
    {
        return $this->css;
    }

    public function hasStationsNavigation(): bool
    {
        return $this->hasStationsNavigation;
    }
}
