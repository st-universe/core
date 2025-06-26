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
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Repository\MapFieldTypeRepository;

#[Table(name: 'stu_map_ftypes')]
#[Index(name: 'map_ftypes_type_idx', columns: ['type'])]
#[Entity(repositoryClass: MapFieldTypeRepository::class)]
class MapFieldType
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

    #[Column(type: 'string', nullable: true)]
    private ?string $complementary_color = '';

    /** @var null|array<FieldTypeEffectEnum>|array<string> */
    #[Column(type: 'json', nullable: true)]
    private ?array $effects = null;

    #[ManyToOne(targetEntity: ColonyClass::class)]
    #[JoinColumn(name: 'colonies_classes_id', referencedColumnName: 'id')]
    private ?ColonyClass $colonyClass = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): MapFieldType
    {
        $this->type = $type;

        return $this;
    }

    public function getIsSystem(): bool
    {
        return $this->is_system;
    }

    public function setIsSystem(bool $isSystem): MapFieldType
    {
        $this->is_system = $isSystem;

        return $this;
    }

    public function getEnergyCosts(): int
    {
        return $this->ecost;
    }

    public function setEnergyCosts(int $energyCosts): MapFieldType
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): MapFieldType
    {
        $this->name = $name;

        return $this;
    }

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): MapFieldType
    {
        $this->damage = $damage;

        return $this;
    }

    public function getSpecialDamage(): int
    {
        return $this->x_damage;
    }

    public function setSpecialDamage(int $specialDamage): MapFieldType
    {
        $this->x_damage = $specialDamage;

        return $this;
    }

    public function getSpecialDamageType(): ?int
    {
        return $this->x_damage_type;
    }

    public function getView(): bool
    {
        return $this->view;
    }

    public function setView(bool $view): MapFieldType
    {
        $this->view = $view;

        return $this;
    }

    public function getPassable(): bool
    {
        return $this->passable;
    }

    public function setPassable(bool $passable): MapFieldType
    {
        $this->passable = $passable;

        return $this;
    }

    public function getComplementaryColor(): ?string
    {
        return $this->complementary_color;
    }

    public function setComplementaryColor(?string $complementaryColor): MapFieldType
    {
        $this->complementary_color = $complementaryColor;
        return $this;
    }

    public function getColonyClass(): ?ColonyClass
    {
        return $this->colonyClass;
    }

    /** @return array<FieldTypeEffectEnum> */
    public function getEffects(): array
    {
        return array_map(
            fn(mixed $effect): FieldTypeEffectEnum => $effect instanceof FieldTypeEffectEnum ? $effect : FieldTypeEffectEnum::from($effect),
            $this->effects ?? []
        );
    }

    /** @param null|array<FieldTypeEffectEnum> $effects */
    public function setEffects(?array $effects): MapFieldType
    {
        $this->effects = $effects;

        return $this;
    }

    public function hasEffect(FieldTypeEffectEnum $effect): bool
    {
        return in_array($effect, $this->getEffects());
    }

    public function getEffectsAsString(): ?string
    {
        if ($this->effects === null) {
            return null;
        }

        return implode("\n", array_map(fn(FieldTypeEffectEnum $effect): string => $effect->value, $this->getEffects()));
    }
}
