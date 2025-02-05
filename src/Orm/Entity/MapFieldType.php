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
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Repository\MapFieldTypeRepository;

#[Table(name: 'stu_map_ftypes')]
#[Index(name: 'map_ftypes_type_idx', columns: ['type'])]
#[Entity(repositoryClass: MapFieldTypeRepository::class)]
class MapFieldType implements MapFieldTypeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $type = 0;

    #[Column(type: 'boolean')]
    private bool $is_system = false;

    #[Column(type: 'smallint')]
    private int $ecost = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $colonies_classes_id = 0;

    #[Column(type: 'smallint')]
    private int $damage = 0;

    #[Column(type: 'smallint')]
    private int $x_damage = 0;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $x_damage_system = 0;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $x_damage_type = null;

    #[Column(type: 'boolean')]
    private bool $view = false;

    #[Column(type: 'boolean')]
    private bool $passable = false;

    /** @var null|array<FieldTypeEffectEnum>|array<string> */
    #[Column(type: 'json', nullable: true)]
    private ?array $effects = null;

    #[ManyToOne(targetEntity: 'ColonyClass')]
    #[JoinColumn(name: 'colonies_classes_id', referencedColumnName: 'id')]
    private ?ColonyClassInterface $colonyClass = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getType(): int
    {
        return $this->type;
    }

    #[Override]
    public function setType(int $type): MapFieldTypeInterface
    {
        $this->type = $type;

        return $this;
    }

    #[Override]
    public function getIsSystem(): bool
    {
        return $this->is_system;
    }

    #[Override]
    public function setIsSystem(bool $isSystem): MapFieldTypeInterface
    {
        $this->is_system = $isSystem;

        return $this;
    }

    #[Override]
    public function getEnergyCosts(): int
    {
        return $this->ecost;
    }

    #[Override]
    public function setEnergyCosts(int $energyCosts): MapFieldTypeInterface
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): MapFieldTypeInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getColonyClassId(): int
    {
        return $this->colonies_classes_id;
    }

    #[Override]
    public function setColonyClassId(int $colonyClassId): MapFieldTypeInterface
    {
        $this->colonies_classes_id = $colonyClassId;

        return $this;
    }

    #[Override]
    public function getDamage(): int
    {
        return $this->damage;
    }

    #[Override]
    public function setDamage(int $damage): MapFieldTypeInterface
    {
        $this->damage = $damage;

        return $this;
    }

    #[Override]
    public function getSpecialDamage(): int
    {
        return $this->x_damage;
    }

    #[Override]
    public function setSpecialDamage(int $specialDamage): MapFieldTypeInterface
    {
        $this->x_damage = $specialDamage;

        return $this;
    }

    #[Override]
    public function getSpecialDamageType(): ?int
    {
        return $this->x_damage_type;
    }

    #[Override]
    public function getView(): bool
    {
        return $this->view;
    }

    #[Override]
    public function setView(bool $view): MapFieldTypeInterface
    {
        $this->view = $view;

        return $this;
    }

    #[Override]
    public function getPassable(): bool
    {
        return $this->passable;
    }

    #[Override]
    public function setPassable(bool $passable): MapFieldTypeInterface
    {
        $this->passable = $passable;

        return $this;
    }

    #[Override]
    public function getColonyClass(): ?ColonyClassInterface
    {
        return $this->colonyClass;
    }

    #[Override]
    public function getEffects(): array
    {
        return array_map(
            fn(mixed $effect): FieldTypeEffectEnum => $effect instanceof FieldTypeEffectEnum ? $effect : FieldTypeEffectEnum::from($effect),
            $this->effects ?? []
        );
    }

    #[Override]
    public function setEffects(?array $effects): MapFieldTypeInterface
    {
        $this->effects = $effects;

        return $this;
    }

    #[Override]
    public function hasEffect(FieldTypeEffectEnum $effect): bool
    {
        return in_array($effect, $this->getEffects());
    }

    #[Override]
    public function getEffectsAsString(): ?string
    {
        if ($this->effects === null) {
            return null;
        }

        return implode("\n", array_map(fn(FieldTypeEffectEnum $effect): string => $effect->value, $this->getEffects()));
    }
}
