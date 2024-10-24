<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
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

    #[Column(type: 'string', length: 100)]
    private string $view;

    #[Column(type: 'integer', nullable: true)]
    private ?int $previous_step_id;

    /** @var array<int> */
    #[Column(type: 'json')]
    private array $next_steps = [];

    /** @var array{elementIds: array<string>, title: string, text: string} */
    #[Column(type: 'json')]
    private array $payload;

    #[Column(type: 'integer')]
    private int $sort = 0;

    #[ManyToOne(targetEntity: 'TutorialStep')]
    #[JoinColumn(name: 'previous_step_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TutorialStepInterface $previousStep;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getPayload(): array
    {
        return $this->payload;
    }

    #[Override]
    public function getPreviousStep(): ?TutorialStepInterface
    {
        return $this->previousStep;
    }

    #[Override]
    public function getNextStepIds(): array
    {
        return $this->next_steps;
    }
}
