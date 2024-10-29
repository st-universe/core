<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Orm\Repository\TutorialStepRepository;

#[Table(name: 'stu_tutorial_step')]
#[Index(name: 'tutorial_view_idx', columns: ['module', 'view'])]
#[Index(name: 'tutorial_sort_idx', columns: ['sort'])]
#[Entity(repositoryClass: TutorialStepRepository::class)]
class TutorialStep implements TutorialStepInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', length: 50, enumType: ModuleViewEnum::class)]
    private ModuleViewEnum $module;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $view = null;

    #[Column(type: 'integer')]
    private int $sort = 0;

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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): ModuleViewEnum
    {
        return $this->module;
    }

    public function setModule(ModuleViewEnum $module): void
    {
        $this->module = $module;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function setView(?string $view): void
    {
        $this->view = $view;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
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
