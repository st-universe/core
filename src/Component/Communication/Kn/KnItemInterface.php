<?php

namespace Stu\Component\Communication\Kn;

use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\KnCharactersInterface;
use Doctrine\Common\Collections\Collection;

interface KnItemInterface
{
    public function getId(): int;

    public function getUser(): UserInterface;

    public function getUserId(): int;

    public function getTitle(): ?string;

    public function getText(): string;

    public function getDate(): int;

    public function getEditDate(): int;

    public function isEditAble(): bool;

    public function getPlot(): ?RpgPlotInterface;

    /**
     * @return Collection<int, KnCharactersInterface>
     */
    public function getCharacters(): Collection;

    public function getCommentCount(): int;

    public function displayContactLinks(): bool;

    public function getUserName(): string;

    public function isNewerThanMark(): bool;

    public function isUserDeleted(): bool;

    public function userCanRate(): bool;

    public function userHasRated(): bool;

    public function getMark(): ?int;

    public function setMark(int $mark): void;

    public function getDivClass(): string;

    public function setIsHighlighted(bool $isHighlighted): void;

    public function getRating(): int;

    public function getRatingBar(): string;

    public function hasTranslation(): bool;
}
