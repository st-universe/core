<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\StuTestCase;

class ColonyMessageBbCodeDefinitionSetTest extends StuTestCase
{
    private ColonyMessageBbCodeDefinitionSet $set;

    #[\Override]
    public function setUp(): void
    {
        $this->set = new ColonyMessageBbCodeDefinitionSet();
    }

    public function testCodeDefinitions(): void
    {
        $definitions = $this->set->getCodeDefinitions();

        $tags = ['i', 'b', 'u', 'h2', 'h3', 'quote', 'color', 'img'];

        foreach ($definitions as $definition) {
            $this->assertTrue(
                in_array(
                    $definition->getTagName(),
                    $tags
                )
            );
        }
    }
}
