<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Stu\StuTestCase;

class KnBbCodeDefinitionSetTest extends StuTestCase
{
    /**
     * @var null|KnBbCodeDefinitionSet
     */
    private $set;

    public function setUp(): void
    {
        $this->set = new KnBbCodeDefinitionSet();
    }

    public function testCodeDefinitions(): void
    {
        $definitions = $this->set->getCodeDefinitions();

        $tags = ['i', 'b', 'u', 'h2', 'h3'];

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
