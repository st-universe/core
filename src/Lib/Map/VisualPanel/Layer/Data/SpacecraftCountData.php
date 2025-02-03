<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Stu\Lib\Map\FieldTypeEffectEnum;

#[Entity]
class SpacecraftCountData extends AbstractData
{
    #[Column(type: 'integer')]
    private int $spacecraftcount = 0;
    #[Column(type: 'integer')]
    private int $cloakcount = 0;

    /** @var null|array<string> */
    #[Column(type: 'json', nullable: true)]
    private ?array $effects = null;

    public function getSpacecraftCount(): int
    {
        return $this->spacecraftcount;
    }

    public function hasCloakedShips(): bool
    {
        return $this->cloakcount > 0;
    }

    public function isEnabled(): bool
    {
        return $this->effects === null
            || !in_array(FieldTypeEffectEnum::NO_SPACECRAFT_COUNT->value, $this->effects);
    }

    public function isDubious(): bool
    {
        return $this->effects !== null
            && in_array(FieldTypeEffectEnum::DUBIOUS_SPACECRAFT_COUNT->value, $this->effects);
    }
}
