<?php

namespace Stu\Orm\Entity;

use Stu\Component\Game\ModuleViewEnum;

interface TutorialStepInterface
{
    public function getId(): int;

    public function getModule(): ModuleViewEnum;

    public function setModule(ModuleViewEnum $module): void;

    public function getView(): ?string;

    public function setView(?string $view): void;

    public function getSort(): int;

    public function setSort(int $sort): void;

    public function getTitle(): ?string;

    public function setTitle(?string $title): void;

    public function getText(): ?string;

    public function setText(?string $text): void;

    public function getElementIds(): ?string;

    public function setElementIds(?string $elementIds): void;

    public function getInnerUpdate(): ?string;

    public function setInnerUpdate(?string $innerUpdate): void;

    public function getFallbackIndex(): ?int;

    public function setFallbackIndex(?int $fallbackIndex): void;
}
