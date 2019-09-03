<?php

namespace Stu\Module\Communication\Lib;

use RPGPlot;
use User;

interface KnPostTalInterface
{
    public function getId(): int;

    public function getUser(): User;

    public function getUserId(): int;

    public function getTitle(): string;

    public function getText(): string;

    public function getDate(): int;

    public function getEditDate(): int;

    public function isEditAble(): bool;

    public function getPlotId(): int;

    public function getRPGPlot(): RPGPlot;

    public function getCommentCount(): int;

    public function displayUserLinks(): bool;

    public function getUserName(): string;

    public function isNewerThanMark(): bool;
}