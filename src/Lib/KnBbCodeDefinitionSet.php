<?php

declare(strict_types=1);

namespace Stu\Lib;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionBuilder;
use JBBCode\CodeDefinitionSet;

final class KnBbCodeDefinitionSet implements CodeDefinitionSet
{
    /** @var CodeDefinition[] The default code definitions in this set. */
    protected $definitions = [];

    /**
     * Constructs the default code definitions.
     */
    public function __construct()
    {
        /* [b] bold tag */
        $builder = new CodeDefinitionBuilder('b', '<strong>{param}</strong>');
        $this->definitions[] = $builder->build();
        /* [i] italics tag */
        $builder = new CodeDefinitionBuilder('i', '<em>{param}</em>');
        $this->definitions[] = $builder->build();
        /* [u] underline tag */
        $builder = new CodeDefinitionBuilder('u', '<u>{param}</u>');
        $this->definitions[] = $builder->build();

    }

    /**
     * Returns an array of the default code definitions.
     *
     * @return CodeDefinition[]
     */
    public function getCodeDefinitions()
    {
        return $this->definitions;
    }
}
