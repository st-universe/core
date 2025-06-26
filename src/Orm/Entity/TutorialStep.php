<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Game\ModuleEnum;
use Stu\Orm\Repository\TutorialStepRepository;

#[Table(name: 'stu_tutorial_step')]
#[Index(name: 'tutorial_view_idx', columns: ['module', 'view'])]
#[Entity(repositoryClass: TutorialStepRepository::class)]
class TutorialStep
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', length: 50, enumType: ModuleEnum::class)]
    private ModuleEnum $module;

    #[Column(type: 'string', length: 100)]
    private string $view = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $next_step_id;

    #[Column(type: 'text', nullable: true)]
    private ?string $title = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $elementIds = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $innerUpdate = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $fallbackIndex = null;

    #[OneToOne(targetEntity: TutorialStep::class, mappedBy: 'nextStep')]
    private ?TutorialStep $previousStep;

    #[OneToOne(targetEntity: TutorialStep::class)]
    #[JoinColumn(name: 'next_step_id', referencedColumnName: 'id')]
    private ?TutorialStep $nextStep;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): ModuleEnum
    {
        return $this->module;
    }

    public function setModule(ModuleEnum $module): void
    {
        $this->module = $module;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getPreviousStepId(): ?int
    {
        $previousStep = $this->getPreviousStep();
        if ($previousStep == null) {
            return null;
        }

        return $previousStep->getId();
    }

    public function getNextStepId(): ?int
    {
        return $this->next_step_id;
    }

    public function getPreviousStep(): ?TutorialStep
    {
        return $this->previousStep;
    }

    public function getNextStep(): ?TutorialStep
    {
        return $this->nextStep;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getElementIds(): ?string
    {
        return $this->elementIds;
    }

    public function setElementIds(?string $elementIds): void
    {
        $this->elementIds = $elementIds;
    }

    public function getInnerUpdate(): ?string
    {
        return $this->innerUpdate;
    }

    public function setInnerUpdate(?string $innerUpdate): void
    {
        $this->innerUpdate = $innerUpdate;
    }

    public function getFallbackIndex(): ?int
    {
        return $this->fallbackIndex;
    }

    public function setFallbackIndex(?int $fallbackIndex): void
    {
        $this->fallbackIndex = $fallbackIndex;
    }
}
