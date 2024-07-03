<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Override;
use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;
use JBBCode\CodeDefinitionSet;

/**
 * Defines all bbcode definitions available in the kn
 */
final class KnBbCodeDefinitionSet implements CodeDefinitionSet
{
    /** @var null|array<CodeDefinition> */
    private ?array $definitions = null;

    /**
     * @return CodeDefinition[]
     */
    #[Override]
    public function getCodeDefinitions(): array
    {
        if ($this->definitions === null) {
            $this->definitions = [
                (new CodeDefinitionBuilder('b', '<strong>{param}</strong>'))->build(),
                (new CodeDefinitionBuilder('i', '<em>{param}</em>'))->build(),
                (new CodeDefinitionBuilder('u', '<u>{param}</u>'))->build(),
                (new CodeDefinitionBuilder('h2', '<span class="knh2">{param}</span>'))->build(),
                (new CodeDefinitionBuilder('h3', '<span class="knh3">{param}</span>'))->build(),
                (new CodeDefinitionBuilder('quote', '<blockquote class="kn">{param}</blockquote>'))->build(),
            ];
        }

        return $this->definitions;
    }
}
