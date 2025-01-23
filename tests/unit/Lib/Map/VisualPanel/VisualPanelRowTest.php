<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\StuTestCase;

class VisualPanelRowTest extends StuTestCase
{
    public function test(): void
    {
        $element1 = mock(VisualPanelElementInterface::class);
        $element2 = mock(VisualPanelElementInterface::class);

        $subject = new VisualPanelRow(42);

        $subject->addEntry($element1);
        $subject->addEntry($element2);

        $this->assertEquals(42, $subject->getY());
        $this->assertEquals([$element1, $element2], $subject->getEntries());
    }
}
