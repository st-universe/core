<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;

interface KnArchiveItemInterface
{
    public function getId(): int;

    public function getFormerId(): int;

    public function getTitle(): ?string;

    public function getText(): string;

    public function getUsername(): string;

    public function getUserId(): int;

    public function getDate(): int;

    public function getEditDate(): ?int;

    public function getRpgPlot(): ?RpgPlotArchiv;

    public function setPlot(?RpgPlotArchiv $plot): void;

    public function getVersion(): ?string;

    public function getPost(): KnPostArchiv;

    /**
     * @return array<mixed>
     */
    public function getRatings(): array;

    public function getRatingBar(): string;

    public function getRating(): int;

    public function userCanRate(): bool;

    public function getCommentCount(): int;

    public function getDivClass(): string;
}
