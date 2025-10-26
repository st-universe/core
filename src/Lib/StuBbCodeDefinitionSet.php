<?php

declare(strict_types=1);

namespace Stu\Lib;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;
use JBBCode\CodeDefinitionSet;
use JBBCode\validators\CssColorValidator;

final class StuBbCodeDefinitionSet implements CodeDefinitionSet
{
    /** @var array<CodeDefinition> */
    private ?array $definitions = null;

    #[\Override]
    public function getCodeDefinitions(): array
    {
        if ($this->definitions === null) {
            $this->definitions = [
                (new CodeDefinitionBuilder('b', '<strong>{param}</strong>'))->build(),
                (new CodeDefinitionBuilder('i', '<em>{param}</em>'))->build(),
                (new CodeDefinitionBuilder('u', '<u>{param}</u>'))->build(),
                (new CodeDefinitionBuilder(
                    'color',
                    '<span style="color: {option}">{param}</span>'
                ))
                    ->setUseOption(true)
                    ->setOptionValidator(new CssColorValidator())
                    ->build()
            ];
        }
        return $this->definitions;
    }
}
