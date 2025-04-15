<?php

namespace Stu\Orm\Entity;

use Stu\Component\Game\ModuleEnum;

interface TutorialStepInterface
{
    public function getId(): int;

    public function getModule(): ModuleEnum;

    public function setModule(ModuleEnum $module): void;

    public function getView(): string;

    public function getPreviousStepId(): ?int;

    public function getNextStepId(): ?int;

    public function getPreviousStep(): ?TutorialStepInterface;

    public function getNextStep(): ?TutorialStepInterface;

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
