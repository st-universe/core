<?php

declare(strict_types=1);

namespace Stu\Lib;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;
use JBBCode\CodeDefinitionSet;
use JBBCode\validators\CssColorValidator;

final class StuBbCodeWithImageDefinitionSet implements CodeDefinitionSet
{
    /** @var CodeDefinition[]|null */
    private ?array $definitions = null;

    #[\Override]
    public function getCodeDefinitions(): array
    {
        $this->definitions ??= [
            new CodeDefinitionBuilder('b', '<strong>{param}</strong>')->build(),
            new CodeDefinitionBuilder('i', '<em>{param}</em>')->build(),
            new CodeDefinitionBuilder('u', '<u>{param}</u>')->build(),
            new CodeDefinitionBuilder(
                'color',
                '<span style="color: {option}">{param}</span>'
            )
                ->setUseOption(true)
                ->setOptionValidator(new CssColorValidator())
                ->build(),
            new CodeDefinitionBuilder('img', '{param}')
                ->setParseContent(false)
                ->build(),
        ];
        return $this->definitions;
    }
}
