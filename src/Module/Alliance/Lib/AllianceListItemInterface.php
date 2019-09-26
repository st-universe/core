<?php

namespace Stu\Module\Alliance\Lib;

interface AllianceListItemInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getFactionId(): ?int;

    public function getMemberCount(): int;

    public function acceptsApplications(): bool;
}
