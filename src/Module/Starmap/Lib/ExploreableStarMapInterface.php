<?php

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\TradePostInterface;

interface ExploreableStarMapInterface
{
    public function getId(): int;

    public function getCx(): int;

    public function getCy(): int;

    public function getFieldId(): int;

    public function getBordertypeId(): ?int;

    public function getUserId(): ?int;

    public function getTitle(): ?string;

    public function getIcon(): ?string;

    public function getHref(): ?string;

    public function setHide(bool $hide): ExploreableStarMapInterface;

    public function getFieldStyle(): string;
}
