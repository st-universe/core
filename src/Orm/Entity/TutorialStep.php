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
use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Orm\Repository\TutorialStepRepository;

#[Table(name: 'stu_tutorial_step')]
#[Index(name: 'tutorial_view_idx', columns: ['module', 'view'])]
#[Entity(repositoryClass: TutorialStepRepository::class)]
class TutorialStep implements TutorialStepInterface
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

    #[OneToOne(targetEntity: 'TutorialStep', mappedBy: 'nextStep')]
    private ?TutorialStepInterface $previousStep;

    #[OneToOne(targetEntity: 'TutorialStep')]
    #[JoinColumn(name: 'next_step_id', referencedColumnName: 'id')]
    private ?TutorialStepInterface $nextStep;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getModule(): ModuleEnum
    {
        return $this->module;
    }

    #[Override]
    public function setModule(ModuleEnum $module): void
    {
        $this->module = $module;
    }

    #[Override]
    public function getView(): string
    {
        return $this->view;
    }

    #[Override]
    public function getPreviousStepId(): ?int
    {
        $previousStep = $this->getPreviousStep();
        if ($previousStep == null) {
            return null;
        }

        return $previousStep->getId();
    }

    #[Override]
    public function getNextStepId(): ?int
    {
        return $this->next_step_id;
    }

    #[Override]
    public function getPreviousStep(): ?TutorialStepInterface
    {
        return $this->previousStep;
    }

    #[Override]
    public function getNextStep(): ?TutorialStepInterface
    {
        return $this->nextStep;
    }

    #[Override]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    #[Override]
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    #[Override]
    public function getText(): ?string
    {
        return $this->text;
    }

    #[Override]
    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    #[Override]
    public function getElementIds(): ?string
    {
        return $this->elementIds;
    }

    #[Override]
    public function setElementIds(?string $elementIds): void
    {
        $this->elementIds = $elementIds;
    }

    #[Override]
    public function getInnerUpdate(): ?string
    {
        return $this->innerUpdate;
    }

    #[Override]
    public function setInnerUpdate(?string $innerUpdate): void
    {
        $this->innerUpdate = $innerUpdate;
    }

    #[Override]
    public function getFallbackIndex(): ?int
    {
        return $this->fallbackIndex;
    }

    #[Override]
    public function setFallbackIndex(?int $fallbackIndex): void
    {
        $this->fallbackIndex = $fallbackIndex;
    }
}
