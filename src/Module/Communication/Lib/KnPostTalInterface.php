<?php

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;

interface KnPostTalInterface
{
    public function getId(): int;

    public function getUser(): UserInterface;

    public function getUserId(): int;

    public function getTitle(): string;

    public function getText(): string;

    public function getDate(): int;

    public function getEditDate(): int;

    public function isEditAble(): bool;

    public function getPlotId(): ?int;

    public function getRPGPlot(): RpgPlotInterface;

    public function getCommentCount(): int;

    public function displayUserLinks(): bool;

    public function getUserName(): string;

    public function isNewerThanMark(): bool;
}