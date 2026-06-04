<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;
use JBBCode\CodeDefinitionSet;
use JBBCode\validators\CssColorValidator;
use Stu\Lib\StuBbCodeImageValidator;

final class ColonyMessageBbCodeDefinitionSet implements CodeDefinitionSet
{
    /** @var null|array<CodeDefinition> */
    private ?array $definitions = null;

    /**
     * @return CodeDefinition[]
     */
    #[\Override]
    public function getCodeDefinitions(): array
    {
        if ($this->definitions === null) {
            $this->definitions = [
                new CodeDefinitionBuilder('b', '<strong>{param}</strong>')->build(),
                new CodeDefinitionBuilder('i', '<em>{param}</em>')->build(),
                new CodeDefinitionBuilder('u', '<u>{param}</u>')->build(),
                new CodeDefinitionBuilder('h2', '<span class="knh2">{param}</span>')->build(),
                new CodeDefinitionBuilder('h3', '<span class="knh3">{param}</span>')->build(),
                new CodeDefinitionBuilder('quote', '<blockquote class="kn">{param}</blockquote>')->build(),
                new CodeDefinitionBuilder(
                    'color',
                    '<span style="color: {option}">{param}</span>'
                )
                    ->setUseOption(true)
                    ->setOptionValidator(new CssColorValidator())
                    ->build(),
                new CodeDefinitionBuilder(
                    'img',
                    '<img src="{param}" style="max-height: 100%;max-width:100%;" />'
                )
                    ->setBodyValidator(new StuBbCodeImageValidator())
                    ->build(),
            ];
        }

        return $this->definitions;
    }
}
