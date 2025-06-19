<?php

namespace Stu\Orm\Entity;

interface DatabaseCategoryAwardInterface
{
    public function getId(): int;

    public function getCategoryId(): int;

    public function setLayerId(?int $layerId): DatabaseCategoryAwardInterface;

    public function getLayerId(): ?int;

    public function setAwardId(?int $awardId): DatabaseCategoryAwardInterface;

    public function getAwardId(): ?int;

    public function getCategory(): DatabaseCategoryInterface;

    public function getAward(): ?AwardInterface;
}
