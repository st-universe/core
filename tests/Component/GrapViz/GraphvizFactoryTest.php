<?php

declare(strict_types=1);

namespace Stu\Component\GrapViz;

use Override;
use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;
use Stu\StuTestCase;

class GraphvizFactoryTest extends StuTestCase
{
    private GraphVizFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new GraphVizFactory();
    }

    public function testCreateGraphReturnsGraph(): void
    {
        static::assertInstanceOf(
            Graph::class,
            $this->subject->createGraph()
        );
    }

    public function testCreateGraphVizReturnsGraphViz(): void
    {
        static::assertInstanceOf(
            GraphViz::class,
            $this->subject->createGraphViz()
        );
    }
}
